# Sprint 4: Módulo de Atención y Recepción

## 4.1 Objetivo del Sprint

Completar el ingreso presencial de pacientes y conectar la reserva de una ficha con la admisión, la valoración de enfermería y la consulta médica. El sistema debe distinguir entre **reservar una ficha**, **registrar la llegada** y **realizar una atención clínica**.

El sprint contempla tanto a pacientes con cita como a pacientes que llegan sin reserva, siempre de acuerdo con las reglas que apruebe el Centro de Salud San Juan. La atención urgente debe seguir el protocolo institucional y nunca depender de que exista una reserva en el sistema.

## 4.2 Contexto y alcance existente

Los Sprints 2 y 3 ya incluyen, en su implementación inicial, consulta de disponibilidad, reservas y citas presenciales para pacientes existentes, agenda médica, recepción inicial de enfermería para citas del día, historial clínico y registro de consulta asociado a una cita.

Por lo tanto, este sprint no propone rehacer esas funciones. Propone ampliar e integrar el flujo para que:

- Recepción encuentre y verifique una reserva, y registre que el paciente llegó.
- Los cambios de estado reflejen el avance real del paciente y estén limitados por rol.
- Un paciente sin cita pueda ingresar al flujo si el centro lo autoriza.
- Enfermería y el médico trabajen sobre la misma atención, sea programada o sin cita.
- La reserva no se confunda con una consulta realizada.

HU14 - Reportes SNIS continúa dependiendo de la especificación oficial del SEDES y no se considera resuelta por este sprint.

## 4.3 Sprint Backlog propuesto

Las historias y estimaciones son una propuesta inicial para planificación; deben revisarse con el centro y el equipo antes de comprometer el sprint.

| Historia | Descripción | Story Points |
|---|---|---:|
| HU17 - Admisión y registro de llegada | Buscar reservas, verificar datos administrativos y registrar la llegada del paciente | 8 |
| HU18 - Atención presencial sin reserva | Buscar o registrar la ficha y crear una atención sin cita, si el centro lo autoriza | 13 |
| HU19 - Flujo y estados de atención | Controlar las transiciones de recepción, enfermería, consulta y finalización | 8 |
| HU20 - Integración clínica por atención | Vincular la recepción de enfermería y la consulta médica a una atención, con o sin cita | 13 |
| HU21 - Consulta del estado y comprobante de reserva | Mostrar un identificador seguro y el estado de la reserva al paciente | 5 |
| **Total provisional** |  | **47** |

El total es una estimación de alcance y no una promesa de entrega. HU18 y HU20 requieren primero acordar y probar el modelo de datos, porque la implementación actual vincula fichas a cuentas de usuario y los registros de enfermería y consulta a citas.

## 4.4 Casos de uso

### CU01 - Reservar una ficha por internet

**Actor principal:** paciente.  
**Estado:** función existente en el alcance de Sprint 2; se conserva y valida como parte del flujo.

1. El paciente consulta especialidades, profesionales y horarios disponibles.
2. Selecciona una especialidad, fecha y horario, y un médico cuando el centro permita elegirlo.
3. El sistema valida que la ficha siga disponible y evita asignar el mismo horario a dos pacientes.
4. El sistema registra la reserva y entrega un identificador y los datos de la cita.
5. La reserva queda en el estado definido por la política del centro.

**Resultado esperado:** existe una reserva identificable, pero no una llegada registrada ni una consulta médica.

### CU02 - Verificar una reserva y registrar la llegada

**Actor principal:** recepcionista o personal de admisión.

1. El personal consulta las reservas del día o busca por identificador de ficha o datos autorizados del paciente.
2. El sistema muestra la fecha, hora, especialidad, médico y estado de la reserva.
3. El personal verifica la identidad con el procedimiento institucional.
4. Si la reserva corresponde y puede recibirse, el personal registra la llegada.
5. El sistema registra usuario y fecha/hora, y actualiza el estado a **En espera** o al estado aprobado por el centro.
6. Recepción orienta al paciente al siguiente paso del protocolo.

**Resultado esperado:** la llegada queda registrada y auditable. No se marca la consulta como atendida.

### CU03 - Recibir a un paciente sin reserva

**Actor principal:** recepcionista o personal autorizado de admisión.  
**Precondición:** el centro habilita la admisión de pacientes sin reserva.

1. Recepción busca una ficha existente por documento u otro dato permitido.
2. Si encuentra una coincidencia, verifica que corresponda al paciente.
3. Si no existe ficha, registra los datos mínimos aprobados por el centro, sin exigir correo ni contraseña de portal.
4. El sistema evita duplicados accidentales y permite resolver coincidencias dudosas según el procedimiento definido.
5. Recepción consulta si hay disponibilidad y registra una solicitud de atención sin cita.
6. La atención queda identificada como **sin reserva** y se deriva al servicio o profesional según las reglas del centro.
7. Si no hay disponibilidad, el personal registra el resultado administrativo permitido y comunica los pasos siguientes.

**Resultado esperado:** se crea una atención presencial sin fabricar una cita ni credenciales. La ficha clínica puede existir aunque el paciente no use el portal.

### CU04 - Continuar la atención con enfermería

**Actor principal:** personal de enfermería.

1. Enfermería consulta la cola de pacientes admitidos según los permisos asignados.
2. Selecciona una atención y verifica los datos mínimos de identificación.
3. Registra el motivo referido y las mediciones que efectivamente tomó, conforme a los protocolos institucionales.
4. El sistema conserva autoría y fecha/hora, y cambia el estado a **En enfermería** o al estado equivalente aprobado.
5. El médico autorizado puede consultar la información inicial vinculada a esa misma atención.

**Resultado esperado:** la recepción de enfermería se relaciona con la atención y no queda limitada a que exista una cita.

### CU05 - Realizar y finalizar la consulta médica

**Actor principal:** médico asignado o autorizado.

1. El médico consulta las atenciones asignadas y la información inicial de enfermería.
2. Registra la consulta médica por separado, incluyendo los campos clínicos habilitados por el sistema.
3. El sistema registra autoría y fecha/hora y cambia el estado a **Atendida** cuando se cumplen las condiciones acordadas.
4. Si corresponde seguimiento, derivación o una nueva reserva, se registra sin alterar la historia original.

**Resultado esperado:** la atención queda vinculada a la consulta realizada. La reserva por sí sola nunca se considera una consulta.

### CU06 - Cancelar o registrar inasistencia

**Actores principales:** paciente y personal autorizado.

- El paciente puede cancelar o reprogramar según las reglas vigentes y los límites de tiempo que apruebe el centro.
- El personal autorizado puede registrar **No se presentó** una vez cumplidas las condiciones definidas.
- El sistema conserva quién hizo el cambio, cuándo y cuál era el estado anterior.
- Una cita cancelada o marcada como inasistencia no debe aparecer como una consulta atendida.

## 4.5 Estados propuestos

Se recomienda mantener separados el **estado de la reserva** y el **estado de la atención**. Una reserva puede cancelarse antes de que exista una atención; una atención sin cita no debería requerir crear una reserva ficticia.

### Estados de reserva

| Estado | Significado |
|---|---|
| Reservada | El sistema asignó una ficha/horario al paciente. |
| Confirmada | La reserva fue validada, solo si el centro requiere confirmación independiente. |
| Cancelada | La reserva se canceló de acuerdo con las reglas autorizadas. |
| No se presentó | El paciente no llegó dentro de las condiciones definidas por el centro. |
| Atendida | La consulta vinculada a la reserva finalizó. |

Si el centro no distingue entre reserva y confirmación, ambos estados pueden consolidarse.

### Estados de atención

| Estado | Significado |
|---|---|
| Recibida | Recepción admitió al paciente; puede ser una atención programada o sin reserva. |
| En espera | El paciente espera el siguiente paso del flujo. |
| En enfermería | Se realiza la valoración inicial, cuando corresponde. |
| Pendiente de consulta | La valoración inicial finalizó y el paciente espera al profesional. |
| En consulta | El profesional inició la atención médica. |
| Atendida | La consulta finalizó y quedó registrada. |
| Derivada | El paciente fue dirigido a otro servicio conforme al procedimiento del centro. |
| No completada | El proceso no llegó a una consulta finalizada; requiere motivo o regla aprobada. |

La transición exacta entre estados, incluidos los casos de ausencia de enfermería, derivación y atención interrumpida, debe definirse antes de implementar HU19.

## 4.6 Roles y permisos

### Paciente

- Consultar especialidades, disponibilidad y reservas propias.
- Recibir un identificador de reserva y sus detalles.
- Cancelar o reprogramar dentro de las reglas aprobadas.
- No consultar fichas, reservas ni datos clínicos de otras personas.

### Recepción / admisión

- El rol operativo encargado de admisión es **Personal administrativo** (`staff`); el administrador también puede realizar estas acciones.
- Consultar reservas necesarias para admitir pacientes.
- Buscar por código o datos identificativos autorizados.
- Verificar datos administrativos y registrar llegadas.
- Crear una solicitud sin reserva solo si tiene permiso y el centro habilita ese flujo.
- No consultar diagnósticos ni notas clínicas salvo autorización institucional expresa.

### Enfermería

- Consultar pacientes pendientes de valoración según la política de acceso.
- Registrar los datos iniciales que le corresponden y comunicar información al profesional responsable.
- No modificar diagnósticos, tratamientos ni registros médicos.

### Médico

- Consultar su agenda y las atenciones que tiene asignadas o autorizadas.
- Revisar los antecedentes y datos de enfermería necesarios para la atención.
- Registrar y finalizar la consulta médica.

### Administrador

- Gestionar usuarios, roles, profesionales, especialidades, horarios y reglas de disponibilidad.
- Configurar permisos de recepción y acceso a reportes administrativos.
- No acceder a datos clínicos por el solo hecho de tener funciones administrativas, salvo que el centro defina y autorice una necesidad específica.

## 4.7 Reglas de negocio

1. Cada reserva y cada atención tienen identificadores únicos.
2. La disponibilidad debe validarse al guardar, dentro de una transacción, para evitar reservas simultáneas del mismo horario.
3. Una reserva no equivale a una llegada ni a una consulta realizada.
4. Registrar llegada requiere permiso de admisión y genera un evento auditable.
5. Los cambios de estado solo pueden realizarse por roles autorizados y en transiciones válidas.
6. El paciente solo puede consultar y modificar las reservas de su propia cuenta.
7. La admisión sin reserva no debe exigir una cita falsa ni una cuenta de portal.
8. La creación de fichas debe buscar coincidencias antes de crear un nuevo paciente.
9. La falta de documento o la duda sobre identidad deben resolverse según reglas institucionales; el sistema no debe inventar identificadores personales.
10. Los registros de enfermería y médicos deben conservar autoría, fecha/hora y trazabilidad de rectificaciones.
11. La prioridad clínica prevalece sobre el orden de llegada cuando así lo determine el protocolo institucional.
12. El sistema no debe diagnosticar, indicar tratamiento ni asignar prioridad clínica automáticamente.
13. La falta de una reserva no debe retrasar la atención de emergencia. El sistema registra datos administrativos solo cuando hacerlo no interfiera con el protocolo asistencial.
14. Los datos personales y clínicos requieren acceso mínimo por rol, auditoría y respaldo conforme a las políticas del centro.

## 4.8 Diseño técnico propuesto

Para soportar citas y pacientes sin reserva sin duplicar flujos, se propone separar:

- **Ficha de paciente:** identidad y datos demográficos; no requiere cuenta de acceso.
- **Cuenta de portal:** credenciales opcionales vinculadas a una ficha.
- **Reserva/cita:** organización de fecha, horario, especialidad y profesional.
- **Atención:** episodio presencial que puede vincularse a una cita o existir sin ella.
- **Eventos de atención:** historial de cambios de estado, autor y fecha/hora.

La atención se convierte en el punto común para la llegada, la recepción de enfermería y la consulta. Antes de migrar las relaciones actuales deben conservarse las fichas, cuentas, citas y registros existentes, garantizando que cada registro clínico siga asociado al paciente correcto.

La implementación debe cubrir también concurrencia al reservar, autorización por rol, validación de datos y compatibilidad con el motor de base de datos elegido para producción. El despliegue en SQL Server u otro motor debe probarse expresamente; las pruebas locales con SQLite no son suficientes para confirmar compatibilidad.

## 4.9 Criterios de aceptación del Sprint

- Recepción encuentra una reserva por identificador o datos autorizados y registra la llegada con autoría y fecha/hora.
- La llegada no marca automáticamente la cita como consulta atendida.
- Las reservas del día muestran especialidad, hora, estado y profesional asignado de acuerdo con los permisos.
- Cuando el centro habilita pacientes sin reserva, recepción puede iniciar una atención sin crear una cita artificial.
- Una ficha nueva puede crearse sin correo ni contraseña, si se aprueba el diseño de ficha independiente de la cuenta.
- El flujo de búsqueda advierte posibles coincidencias para reducir duplicados.
- Enfermería puede registrar los datos iniciales permitidos tanto para una atención programada como para una atención sin cita.
- El médico autorizado puede consultar la recepción de enfermería y registrar su consulta en la misma atención.
- Los estados no pueden cambiarse fuera de las transiciones autorizadas y los cambios quedan auditados.
- Cancelaciones e inasistencias no se contabilizan como consultas atendidas.
- La simultaneidad de reservas no permite asignar una ficha por encima de la capacidad disponible.
- Los permisos impiden que pacientes y personal sin autorización consulten información clínica.
- Las pruebas cubren los flujos programado y sin cita, roles, duplicidades, concurrencia y preservación de datos existentes.

## 4.10 Fuera de alcance inicial

- Definir o sustituir protocolos de emergencia, triaje o priorización clínica.
- Diagnóstico o recomendación terapéutica automática.
- Integración completa con laboratorio, farmacia u otros servicios complementarios; podrán modelarse como una fase posterior si el centro lo requiere.
- Formatos definitivos de reportes SNIS sin especificación del SEDES.
- Envío de notificaciones por SMS, WhatsApp o correo hasta que se definan canal, consentimiento y configuración institucional.
- Consolidación automática de fichas duplicadas sin revisión y autorización de personal designado.

## 4.11 Decisiones que debe validar el centro

1. ¿Quién puede admitir pacientes sin reserva: recepción, administración u otro cargo?
2. ¿Se permite elegir médico al reservar o el sistema asigna profesional por disponibilidad?
3. ¿Qué datos son obligatorios para crear la ficha y cómo se identifica a quien no presenta documento?
4. ¿Cómo se confirma una reserva y se define el límite para marcar una inasistencia?
5. ¿Qué servicios pueden recibir pacientes sin cita y quién decide su asignación?
6. ¿Puede enfermería comenzar una valoración antes de que se asigne médico?
7. ¿Qué estados y transiciones se usarán en la operación diaria?
8. ¿Quién puede corregir datos administrativos y cómo se rectifican registros clínicos?
9. ¿Qué información verá recepción en pantalla y en reportes?
10. ¿Cómo se documentan las urgencias sin retrasar la atención?
11. ¿Se incluirán laboratorio, farmacia u otros servicios dentro de este sprint o en una fase posterior?
12. ¿Cuál será el motor de base de datos y el entorno de producción definitivo?

## 4.12 Estado de implementación

La primera implementación del Sprint 4 quedó integrada con los módulos existentes. Incluye:

- Acceso al módulo de admisión para el rol Personal administrativo (`staff`) y administración. El rol anterior `receptionist` se conserva temporalmente solo para compatibilidad con cuentas existentes.
- Búsqueda de reservas por código, documento o nombre; registro presencial de llegada con autoría, fecha/hora y evento auditable.
- Código único para cada reserva y registro de inasistencia cuando ya pasó la hora programada.
- Atención sin reserva para una ficha existente seleccionada desde los resultados de búsqueda o para un paciente nuevo sin cuenta de portal. En ambos casos se crea una atención, no una cita ficticia.
- Flujo común de atención para recepción, enfermería y médico, con historial de cambios de estado.
- Registro de enfermería y consulta médica vinculados a la misma atención, tanto para una reserva como para una llegada sin cita.
- Restricción de las rutas por rol y acceso del médico a las atenciones que tiene asignadas.
- Pruebas de flujo y regresión para recepción, enfermería, consulta médica, pacientes e historial.

### Límites que siguen pendientes de validación institucional

- La creación de una ficha nueva exige documento de identidad; no se admite todavía un paciente sin documento porque el centro debe definir cómo identificarlo.
- La inasistencia puede registrarse desde la hora programada; no se configuró un período de gracia porque no existe una política aprobada.
- El sistema no implementa triaje automático ni sustituye el protocolo de urgencias. La prioridad clínica y la derivación siguen siendo decisiones del personal sanitario.
- La asignación de un médico para una atención sin cita se elige en recepción; no se implementó un motor de disponibilidad para walk-ins.
- La suite local se valida con SQLite. La compatibilidad de migraciones y transacciones debe probarse con el motor que se acuerde para producción.
- Las migraciones no se han ejecutado contra producción y el flujo debe validarse con recepción, enfermería y personal médico antes de su uso operativo.

## 4.13 Orden sugerido de implementación

1. Validar con el centro los roles, los datos mínimos, los estados y las políticas de admisión ya reflejados en la primera versión.
2. Probar la separación de ficha, cuenta, reserva y atención con una copia controlada de datos antes de aplicar migraciones en producción.
3. Confirmar reglas para pacientes sin documento, período de gracia de inasistencia y derivación de urgencias.
4. Ejecutar las migraciones y pruebas de regresión con el motor de producción acordado.
5. Validar el flujo completo con recepción, enfermería y personal médico antes de habilitarlo operativamente.

## Conclusión

El Sprint 4 integra el recorrido **reserva (cuando existe), admisión, espera, enfermería cuando corresponde, consulta médica y finalización**. La primera implementación separa la reserva, la llegada y la atención clínica, y conserva sus eventos y responsables.

La ampliación principal permite admitir pacientes con o sin reserva sin crear cuentas de portal o citas ficticias, y vincula sus registros de enfermería y consulta a una atención. Los límites institucionales y la validación con el motor de producción descritos arriba siguen siendo necesarios antes del despliegue.
