# Propuesta: registro inicial de enfermería y signos vitales

## Opinión

La idea es adecuada y puede mejorar el flujo de atención: enfermería recibe al paciente, registra los datos iniciales y el médico los consulta antes o durante la consulta. Así se evita que esa información quede en papel o tenga que recopilarse nuevamente.

Recomiendo que enfermería registre **datos de recepción y mediciones observables**, y que el médico conserve la responsabilidad de valorar esa información, determinar diagnósticos y registrar el tratamiento. La captura de enfermería debe complementar la consulta, no reemplazar la historia clínica ni permitir que un rol realice acciones propias de otro.

## Flujo de la primera versión

1. En la primera versión, el paciente tiene una cita programada para ese día. La atención sin cita queda fuera del alcance implementado y requiere definir el procedimiento del centro.
2. Enfermería abre la bandeja diaria, selecciona la cita y confirma la identidad del paciente.
3. Enfermería registra la fecha y hora de recepción, el motivo referido por el paciente y los signos vitales que efectivamente haya medido.
4. El sistema guarda el registro asociado al paciente, a la cita y a la cuenta de enfermería que lo creó.
5. La información queda visible para el médico asignado desde su agenda.
6. El médico revisa los datos y registra por separado sus observaciones, valoración, diagnóstico, tratamiento y recomendaciones.

La propuesta inicial también recomendó estados como **Pendiente de atención**, **Datos iniciales registrados** y **Consulta médica completada**. Esos estados de flujo todavía no están implementados en HU16 y deben definirse e integrarse con HU13 si el centro los requiere. Los datos de enfermería no se presentan como diagnóstico médico.

## Datos iniciales posibles

El centro debe aprobar qué campos son necesarios y cómo se medirán. Como punto de partida, se pueden evaluar:

- Fecha y hora de recepción.
- Motivo de consulta expresado por el paciente, sin convertirlo automáticamente en un diagnóstico.
- Presión arterial sistólica y diastólica, si se mide.
- Frecuencia cardíaca.
- Temperatura y unidad utilizada.
- Frecuencia respiratoria.
- Saturación de oxígeno, si se dispone del equipo.
- Peso y talla cuando sean pertinentes al protocolo del centro.
- Observaciones breves de enfermería relacionadas con la recepción.

Conviene guardar cada medición como un valor estructurado con su unidad, fecha y hora. No es recomendable guardar todos los signos vitales en una sola nota de texto, porque después resultaría difícil consultarlos, compararlos o incluirlos en reportes.

## Alcance y permisos recomendados

### Enfermería

- Consultar los datos mínimos del paciente necesarios para verificar su identidad y atenderlo.
- Ver las citas necesarias para realizar la recepción, conforme a las reglas del centro.
- Crear el registro inicial y guardar las mediciones que tomó.
- Registrar datos iniciales en las citas activas del día y abrir su formulario para consultar o añadir una rectificación.
- No modificar diagnósticos, tratamientos ni notas médicas.
- Si detecta un error en un registro ya guardado, solicitar una corrección o agregar una rectificación trazable, sin borrar el dato original.

### Médico

- Ver los datos iniciales relacionados con sus pacientes y citas.
- Registrar la consulta médica de forma separada.
- Dejar constancia de que revisó la información, si el centro considera necesario ese control.

### Administración

- Gestionar cuentas y la programación de citas según sus permisos actuales.
- No acceder por defecto a detalles clínicos que no necesite para su trabajo administrativo.

La visibilidad debe limitarse al personal autorizado y al propósito asistencial. Los registros deberían conservar quién los creó y cuándo; la política de auditoría y conservación debe definirse con el centro.

## Incorporación al Sprint 3

El Sprint 3 ya contempla el historial clínico (HU12) y el registro de consulta médica (HU13). Recomiendo incorporar el ingreso inicial de enfermería como una parte explícita del flujo de atención, coordinada con esas historias:

- **HU12 - Historial clínico:** incluir una sección de registros iniciales y mediciones previas, con acceso controlado.
- **HU13 - Registro de consulta:** permitir que el médico revise los datos de enfermería antes de completar su valoración clínica.
- **HU16 - Recepción de enfermería:** crear y consultar el registro inicial asociado a una cita activa del día, conservar las mediciones y permitir rectificaciones trazables.

Para la primera versión, la bandeja de enfermería muestra todas las citas pendientes o confirmadas del día. No existe todavía una asignación de enfermera a cita; por eso cualquier usuario con rol de enfermería puede hacer la recepción de las citas del día. Cada registro queda vinculado al médico responsable de la cita, y solo ese médico puede consultar sus datos iniciales.

HU16 se estima provisionalmente en **6 Story Points** dentro del Sprint 3, sujeto a refinamiento y aceptación del centro. La incorporación eleva el alcance planificado de 31 a 37 Story Points.

## Criterios de aceptación implementados en HU16

- Una enfermera autenticada puede registrar datos iniciales para una cita activa del día.
- El registro queda relacionado con el paciente, la cita, la cuenta que lo ingresó y la fecha/hora.
- Los campos numéricos se validan y muestran claramente sus unidades; la presión arterial requiere ambos valores o ninguno.
- El médico asignado puede consultar los datos desde su agenda; otros médicos no pueden abrir ese registro.
- El formulario de enfermería no guarda campos clínicos no permitidos, como un diagnóstico.
- Un paciente no puede consultar los datos iniciales mediante las rutas de enfermería o de lectura médica.
- Las rectificaciones se agregan con motivo y autor, sin sobrescribir el registro inicial; el médico puede ver ambas versiones.
- El sistema no genera diagnósticos ni recomendaciones clínicas automáticas a partir de valores ingresados.

## Decisiones pendientes de validación institucional

Aunque la captura ya está desarrollada, el centro todavía debe confirmar:

1. Definir si los signos vitales opcionales en el formulario deben variar según el tipo de atención.
2. Definir cómo se reciben los pacientes sin cita y cómo se asignan al médico responsable.
3. Decidir si en el futuro se asignará una enfermera específica a cada cita; actualmente todas las cuentas con rol de enfermería pueden atender la cola diaria.
4. Confirmar si basta el acceso al registro desde la agenda médica o si se necesita una alerta visual adicional.
5. Aprobar el protocolo ante valores que requieran atención prioritaria.
6. Definir cuánto tiempo conservar los datos y qué trazabilidad exige la institución.

El sistema no debe decidir por sí solo si un valor representa una emergencia. Cualquier umbral, alerta o protocolo debe ser definido y aprobado por profesionales responsables del centro y reflejar sus procedimientos vigentes. Los límites técnicos de validación de los campos sirven para evitar datos imposibles de almacenar; no son rangos de normalidad ni criterios de diagnóstico.

## Estado actual del proyecto

El flujo de HU16 ya está implementado como avance del Sprint 3: bandeja diaria, captura de motivo referido y signos vitales opcionales, visualización restringida al médico responsable y rectificaciones que conservan las versiones previas. Las citas sin turno, la asignación de enfermera a cita, las alertas clínicas y los diagnósticos automáticos no forman parte de este alcance. La aceptación operativa, los protocolos clínicos y la validación en SQL Server todavía deben confirmarse con el centro.
