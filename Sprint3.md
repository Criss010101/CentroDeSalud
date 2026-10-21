# 3.4 Plan del Sprint 3

## 3.4.1 Objetivo del Sprint

Completar el flujo de atención médica del Centro de Salud San Juan mediante el acceso controlado a antecedentes, el registro de consultas, los reportes institucionales y la preparación técnica para el despliegue. El sprint incorpora además la recepción inicial de enfermería para que el médico asignado disponga de las mediciones registradas antes de documentar su valoración.

La recepción de enfermería ya cuenta con un adelanto técnico de HU16 y se integró inicialmente con el historial clínico y el registro de consulta. La aceptación operativa, las reglas institucionales de corrección y la integración final continúan pendientes.

## 3.4.2 Sprint Backlog

| Historia | Descripción | Story Points |
|---|---|---:|
| HU12 - Acceso a Historial Clínico | Consultar antecedentes y consultas anteriores con acceso restringido al personal autorizado | 5 |
| HU13 - Registro de Consulta | Registrar motivo, observaciones médicas, diagnóstico, tratamiento y recomendaciones | 13 |
| HU14 - Reportes SNIS | Consolidar y exportar estadísticas según formatos confirmados por el SEDES | 8 |
| HU15 - Reportes Institucionales | Generar reportes de pacientes, citas y rendimiento | 5 |
| HU16 - Recepción de Enfermería | Capturar los datos iniciales de una cita, compartirlos con el médico responsable y conservar rectificaciones trazables | 6 |
| **Total planificado** |  | **37** |

La estimación inicial de HU12–HU15 era de 31 Story Points. HU16 se agrega con una estimación provisional de 6 puntos; el equipo debe confirmar la capacidad y ajustar el alcance en la planificación del sprint.

### Estado de implementación

| Historia | Estado | Alcance disponible |
|---|---|---|
| HU12 - Historial Clínico | Implementación inicial | El médico con una cita no cancelada del paciente puede consultar sus consultas anteriores y datos iniciales de enfermería. |
| HU13 - Registro de Consulta | Implementación inicial | El médico asignado registra motivo, observaciones, diagnóstico, tratamiento y recomendaciones para una cita confirmada que ya inició. La cita pasa a estado atendido. |
| HU14 - Reportes SNIS | Pendiente de especificación oficial | Falta recibir del SEDES el formato, las variables, los períodos y el mecanismo de entrega requeridos. |
| HU15 - Reportes Institucionales | Implementación inicial | Reporte mensual agregado de altas de pacientes, citas por estado y actividad por médico, visible y exportable en CSV para administración. |
| HU16 - Recepción de Enfermería | Implementada inicialmente | Bandeja diaria, captura, acceso del médico responsable y rectificaciones trazables; falta aceptación operativa. |

El indicador de actividad de HU15 expresa únicamente la proporción de citas con consulta registrada; no califica la calidad ni los resultados clínicos del médico. El reporte no expone listados de pacientes ni diagnósticos.

## 3.4.3 HU16 - Recepción inicial de enfermería

### Flujo

1. Enfermería abre la bandeja de citas pendientes o confirmadas del día.
2. Selecciona al paciente y revisa la identidad y el médico responsable que aparecen asociados a la cita.
3. Registra el motivo expresado por el paciente y solo las mediciones que efectivamente haya tomado.
4. El sistema relaciona el registro con la cita, el paciente, el autor y la fecha/hora de captura.
5. El médico asignado encuentra un acceso al registro desde su agenda y consulta la versión vigente y las anteriores.
6. Si hay un error, enfermería añade una rectificación con motivo. El sistema conserva el registro original y cada versión posterior.
7. El médico realiza por separado su valoración y, en HU13, registra diagnóstico, tratamiento y recomendaciones.

### Datos capturados

- Motivo referido por el paciente.
- Presión sistólica y diastólica, registradas juntas o ambas omitidas.
- Frecuencia cardíaca.
- Temperatura en grados Celsius.
- Frecuencia respiratoria.
- Saturación de oxígeno.
- Peso en kilogramos y talla en centímetros.
- Observaciones breves de recepción.

Los signos vitales son opcionales individualmente; el sistema no inventa valores ni los interpreta como diagnóstico. Las validaciones de entrada controlan el formato y límites técnicos del campo, no representan rangos clínicos de normalidad.

### Permisos y alcance acordados para la primera versión

- Enfermería puede consultar las citas pendientes o confirmadas del día y capturar datos iniciales.
- No hay asignación de enfermera a una cita en esta versión; el acceso a la cola diaria corresponde a usuarios autenticados con rol de enfermería.
- El médico solo puede consultar la recepción vinculada a sus propias citas.
- Pacientes, personal administrativo y médicos distintos del responsable no pueden consultar el registro mediante estas rutas.
- Enfermería no registra ni modifica diagnósticos, tratamientos o recomendaciones médicas.
- Las correcciones se añaden como rectificaciones con autor, fecha y motivo; no se ofrece una operación para sobrescribir o borrar el registro clínico.
- La primera versión cubre citas activas del día; no implementa pacientes sin cita ni asignación avanzada de turnos de enfermería.

### Criterios de aceptación

- Enfermería registra un motivo y cualquier subconjunto de mediciones válidas para una cita activa del día.
- Una cita solo puede tener un registro inicial; un segundo intento de creación no sobrescribe la captura.
- La presión arterial se guarda con ambos valores o sin ninguno.
- El médico responsable puede consultar el registro desde su agenda; otro médico recibe una respuesta de no encontrado.
- La bandeja de enfermería no muestra citas futuras ni canceladas.
- Cada rectificación exige un motivo, identifica al autor y conserva el contenido de las versiones anteriores.
- Las entradas del paciente se escapan al presentarlas y no se aceptan campos clínicos no permitidos, como un diagnóstico enviado desde el formulario de enfermería.
- El sistema no calcula diagnósticos ni activa alertas clínicas automáticas.

## 3.4.4 Dependencias y decisiones pendientes

- HU13 debe integrarse con los datos de recepción de HU16, manteniendo separados los registros de enfermería y la valoración médica.
- HU12 debe definir cómo se muestran cronológicamente los datos iniciales junto con las consultas y antecedentes clínicos.
- El centro debe validar los campos, las unidades, los protocolos de atención y la política institucional de conservación de los registros.
- El SEDES debe confirmar el formato, las variables, el período y el medio oficial de entrega de SNIS antes de desarrollar HU14.
- El centro debe definir cómo se rectifican consultas médicas ya guardadas; por ahora HU13 impide duplicar una consulta por cita y no permite modificarla.
- Antes del despliegue debe probarse la integridad referencial, las consultas y el comportamiento de concurrencia con SQL Server.
- Debe acordarse la plataforma de producción: el contexto inicial solicita Laravel 11/PHP 8.2 y SQL Server, mientras la aplicación instalada utiliza Laravel 13/PHP 8.4, la base local es SQLite y la guía de VPS describe MariaDB.
- La asignación de una enfermera a una cita y la recepción de pacientes sin cita quedan fuera del alcance inicial y requieren una decisión del centro.

## 3.4.5 Verificación técnica del Sprint

La implementación incluye historial clínico y consulta médica vinculados a la agenda, acceso de lectura limitado al médico que tiene una cita con el paciente, reportes institucionales agregados con descarga CSV para administración y recepción de enfermería con rectificaciones. Las pruebas cubren permisos, aislamiento entre médicos, validación de datos clínicos, registro único de consulta por cita, cálculo de agregados y escape de datos presentados.

En la verificación local actual, las **45 pruebas pasan con 315 aserciones** y la compilación de frontend termina correctamente. Estas comprobaciones no sustituyen la aceptación funcional del centro ni las pruebas sobre SQL Server.

## 3.4.6 Riesgos y límites

- La configuración de la cola para todas las enfermeras del día debe validarse con la política interna de acceso del centro.
- La aplicación no reemplaza el juicio clínico ni define qué valores requieren atención prioritaria.
- El registro inicial no habilita todavía la atención sin cita.
- Los datos clínicos deben protegerse con los controles de acceso, copias de seguridad y políticas de conservación definidos para producción.
- Los informes institucionales y SNIS no deben considerarse definitivos hasta que sus requisitos sean confirmados por las autoridades correspondientes.
- Las consultas médicas guardadas no tienen todavía un flujo de rectificación trazable; debe aprobarse e implementarse antes del uso real si la política clínica lo exige.

## Conclusión

El Sprint 3 ya cuenta con una primera implementación de HU12, HU13 y HU15, además de HU16. Para cerrar el sprint faltan la especificación oficial e implementación de HU14, acordar la corrección trazable de consultas médicas, validar los flujos con el centro y decidir y probar la plataforma de producción.
