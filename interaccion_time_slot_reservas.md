**1. Interacción entre `time_slots` y `reservations` para Mostrar Disponibilidad:**

La tabla `time_slots` define *qué* horas son *potencialmente* reservables para una pista y su precio base. La tabla `reservations` registra qué horas específicas, en qué días específicos, ya están *ocupadas* (o temporalmente bloqueadas).

El proceso para mostrarle al usuario los slots disponibles para un **día específico** (por ejemplo, 2023-12-18) en el calendario sería así:

1.  **Usuario Selecciona Día:** El usuario hace clic en el día `2023-12-18` para la `Pista X`.
2.  **Obtener Slots Potenciales:** Tu aplicación consulta la tabla `time_slots`:
    *   `SELECT id, start_time, end_time, price FROM time_slots WHERE pista_id = X AND is_available = true ORDER BY start_time;`
    *   Esto te da la lista de todos los bloques horarios que el *cliente dueño de la pista* ha definido como disponibles en general (ej: 09:00-10:00, 10:00-11:00, 11:00-12:00,...).
3.  **Obtener Slots Ya Ocupados para ESE Día:** Tu aplicación consulta la tabla `reservations`:
    *   `SELECT start_time FROM reservations WHERE pista_id = X AND reservation_date = '2023-12-18' AND status IN ('confirmed', 'awaiting_payment');`
    *   Esto te da una lista de las horas de inicio que ya están reservadas o en proceso de pago para ese día específico (ej: 10:00:00).
    *   *Nota:* Incluir `'awaiting_payment'` es crucial para bloquear slots que alguien más está intentando pagar en ese momento. Las reservas `'awaiting_payment'` que excedan `expires_at` deberían ser eliminadas por tu tarea programada, por lo que no deberían aparecer aquí si la tarea funciona bien, pero incluirlas en el filtro `IN` es seguro.
4.  **Filtrar y Mostrar:** La aplicación compara las dos listas:
    *   Itera sobre los *Slots Potenciales* (del paso 2).
    *   Para cada slot potencial (ej: 09:00-10:00), comprueba si su `start_time` (09:00:00) existe en la lista de *Slots Ya Ocupados* (del paso 3).
    *   Si **NO** existe, ese slot (09:00-10:00 para el 2023-12-18) está **DISPONIBLE** y se muestra al usuario (probablemente con su `price`).
    *   Si **SÍ** existe (como el de las 10:00:00 en nuestro ejemplo), ese slot está **OCUPADO** y no se muestra o se muestra como no disponible.

**En resumen:** `time_slots` te da la plantilla base de horarios, y `reservations` te dice cuáles de esos horarios ya están cogidos para el día concreto que el usuario está mirando.

**2. ¿Cómo Evitar que Aparezcan Slots los Fines de Semana?**

Tienes varias formas de lograr esto, dependiendo de la flexibilidad que necesites:

**Opción A: Lógica en la Aplicación (La más simple si la regla es fija)**

Si NUNCA quieres que haya reservas los sábados o domingos para NINGUNA pista, puedes añadir esta lógica directamente cuando generas el calendario o cuando consultas la disponibilidad.

*   **Al mostrar el calendario:** Simplemente deshabilita los sábados y domingos para que el usuario no pueda ni seleccionarlos.
*   **Al consultar disponibilidad (Paso 3 o 4 anterior):** Antes de mostrar los slots disponibles, comprueba el día de la semana de la `reservation_date` seleccionada. Si es sábado (día 6) o domingo (día 0 o 7, dependiendo de la librería/configuración), simplemente no muestres ningún slot, independientemente de lo que digan `time_slots` o `reservations`.

    ```php
    // Ejemplo en un controlador de Laravel (simplificado)
    use Carbon\Carbon;

    public function getAvailableSlots(Pista $pista, string $dateString)
    {
        $selectedDate = Carbon::parse($dateString);

        // *** Lógica de fin de semana ***
        if ($selectedDate->isWeekend()) {
            return response()->json([]); // Devuelve array vacío, no hay slots disponibles
        }
        // *****************************

        $potentialSlots = TimeSlot::where('pista_id', $pista->id)
                                  ->where('is_available', true)
                                  ->orderBy('start_time')
                                  ->get(['id', 'start_time', 'end_time', 'price']);

        $bookedStartTimes = Reservation::where('pista_id', $pista->id)
                                    ->where('reservation_date', $selectedDate->toDateString())
                                    ->whereIn('status', ['confirmed', 'awaiting_payment'])
                                    // ->where(function($q) { // Más seguro con expires_at
                                    //     $q->where('status', 'confirmed')
                                    //       ->orWhere(function($q2){
                                    //           $q2->where('status', 'awaiting_payment')
                                    //              ->where('expires_at', '>', now());
                                    //       });
                                    // })
                                    ->pluck('start_time') // Obtiene solo las horas de inicio
                                    ->map(fn($time) => substr($time, 0, 8)); // Asegura formato HH:MM:SS

        $availableSlots = $potentialSlots->filter(function ($slot) use ($bookedStartTimes) {
            // Comprueba si la hora de inicio del slot potencial NO está en las horas reservadas
            return !$bookedStartTimes->contains(substr($slot->start_time, 0, 8));
        });

        return response()->json($availableSlots->values()); // Devuelve los slots filtrados
    }
    ```

**Opción B: Modificar la Tabla `time_slots` (Más Flexible)**

Si quieres permitir que *algunos* clientes sí ofrezcan pistas en fin de semana, o tener precios diferentes, necesitas almacenar esa información en la base de datos.

*   **Añadir Días de la Semana:** Añade una columna a `time_slots`, por ejemplo `applicable_days` (podría ser un `VARCHAR` o `JSON`).
    *   Podrías almacenar los días como una cadena separada por comas (`"1,2,3,4,5"`) o un array JSON (`[1, 2, 3, 4, 5]`, donde Lunes=1, Martes=2,... Domingo=7).
    *   En la consulta del **Paso 2 (Obtener Slots Potenciales)**, añadirías una condición para filtrar solo los `time_slots` cuyo campo `applicable_days` contenga el número del día de la semana correspondiente a la `reservation_date` seleccionada.

    ```php
    // Migración para añadir la columna (ejemplo con JSON)
    Schema::table('time_slots', function (Blueprint $table) {
        // Guarda array de números de día [1, 2, 3, 4, 5, 6, 7]
        $table->json('applicable_days')->nullable()->after('is_available');
        // O usar VARCHAR: $table->string('applicable_days_csv')->default('1,2,3,4,5')->after('is_available');
    });

    // Consulta modificada (ejemplo con JSON y Carbon)
    $dayOfWeek = $selectedDate->dayOfWeekIso; // Lunes=1, Domingo=7

    $potentialSlots = TimeSlot::where('pista_id', $pista->id)
                              ->where('is_available', true)
                              // Condición para los días aplicables
                              ->whereJsonContains('applicable_days', $dayOfWeek)
                              // Si usaras CSV: ->where('applicable_days_csv', 'like', '%'.$dayOfWeek.'%') // ¡Ojo! menos eficiente y propenso a errores ('1' coincide con '10') - Mejor usar find_in_set si es MySQL o lógica más robusta
                              ->orderBy('start_time')
                              ->get(['id', 'start_time', 'end_time', 'price']);
    ```
    *   El cliente podría entonces, al definir sus `time_slots`, marcar en qué días de la semana aplica cada uno. Si no marca Sábado y Domingo para un slot, no aparecerá esos días.

**Recomendación:**

*   Si la regla de "no fines de semana" es global y simple, empieza con la **Opción A (Lógica en la Aplicación)**. Es más rápido de implementar.
*   Si prevés que necesitarás flexibilidad (permitir fines de semana para algunos, precios distintos, etc.), la **Opción B (Modificar `time_slots`)** es más escalable y correcta a largo plazo, aunque requiere un cambio en la base de datos y en la interfaz donde el cliente gestiona sus horarios.

Elige la opción que mejor se adapte a los requisitos actuales y futuros de tu proyecto.