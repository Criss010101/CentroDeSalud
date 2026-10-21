**Rol:** Actúa como un Equipo de Desarrollo Full Stack experto en metodologías ágiles (Scrum).

**Contexto del Proyecto:**
Vamos a desarrollar el "Sistema Web para la Gestión y Control de la Información del Centro de Salud San Juan". Has finalizado el Sprint 0 (Planificación y Arquitectura) y ahora iniciaremos la ejecución de los Sprints 1, 2 y 3. El proyecto debe estar 100% finalizado y listo para producción al terminar el Sprint 3.

**Stack Tecnológico Obligatorio:**
*   **Backend:** PHP 8.2 + Framework Laravel 11 (Patrón MVC).
*   **Frontend:** HTML5, CSS3, JavaScript, Tailwind CSS.
*   **Base de Datos:** SQL Server (MSSQL).
*   **Infraestructura (Mentalidad de despliegue):** Preparado para VPS.

**Metodología de Trabajo (Scrum):**
Te presentaré el Sprint Backlog dividido en 3 iteraciones. Tu objetivo es desarrollar el código (Migraciones, Modelos, Controladores, Vistas y Rutas) para cada historia de usuario. 
*Importante:* Trabajaremos Sprint por Sprint. Cuando te pida iniciar un Sprint, me entregarás el código necesario para completarlo y esperarás mi validación antes de pasar al siguiente.

---

### 📦 PRODUCT BACKLOG DIVIDIDO POR SPRINTS

#### 🏃‍♂️ SPRINT 1: Gestión de Usuarios y Pacientes (Duración estimada: 21 Story Points)
*Objetivo del Sprint:* Tener un sistema base funcional con autenticación de roles y gestión completa de las fichas de los pacientes.

*   **HU01 - Autenticación (3 SP):** Login seguro para Paciente, Médico o Administrador según sus credenciales.
*   **HU02 - Gestión de Roles (5 SP):** CRUD para que el Administrador registre, modifique y asigne roles a los usuarios.
*   **HU03 - Registro de Paciente (5 SP):** Formulario para que el paciente o administrador registre los datos personales y cree la ficha.
*   **HU04 - Modificación de Ficha (5 SP):** Vista para que el paciente actualice sus datos personales.
*   **HU05 - Búsqueda de Pacientes (3 SP):** Buscador con filtros para el Personal Administrativo y Médicos.

#### 🏃‍♂️ SPRINT 2: Personal Médico, Especialidades y Citas (Duración estimada: 29 Story Points)
*Objetivo del Sprint:* Estructurar la oferta médica del centro y habilitar el motor de reservas de citas para pacientes y administración.

*   **HU06 - Registro de Personal (5 SP):** CRUD para registrar médicos, enfermeras y administrativos con sus datos laborales.
*   **HU07 - Gestión de Especialidades (3 SP):** CRUD para crear especialidades y asignarlas a los médicos.
*   **HU08 - Visualización de Disponibilidad (5 SP):** Interfaz para que el paciente vea especialidades, médicos y horarios libres.
*   **HU09 - Programación de Cita - Paciente (8 SP):** Motor para agendar, confirmar o cancelar citas médicas por parte del paciente.
*   **HU10 - Programación de Cita - Administración (5 SP):** Interfaz para que administración agende citas a pacientes presenciales.
*   **HU11 - Consulta de Agenda (3 SP):** Tablero para que el médico visualice sus citas programadas del día.

#### 🏃‍♂️ SPRINT 3: Consultas, Historias Clínicas, Reportes y Cierre (Duración estimada: 31 Story Points)
*Objetivo del Sprint:* Finalizar el flujo de atención médica, generar los reportes estadísticos exigidos y dejar el sistema listo para producción.

*   **HU12 - Acceso a Historial Clínico (5 SP):** Interfaz para que el médico revise antecedentes y tratamientos previos del paciente.
*   **HU13 - Registro de Consulta (13 SP):** Formulario clínico para que el médico registre motivo, observaciones, diagnóstico, tratamiento y recomendaciones (Actualización de Historia Clínica).
*   **HU14 - Reportes SNIS (8 SP):** Generación de reportes estadísticos consolidados para el SEDES.
*   **HU15 - Reportes Institucionales (5 SP):** Generación de reportes internos (pacientes, citas, rendimiento).
*   **Cierre de Sprint (Tareas Técnicas):** Refactorización final, pruebas de integración y configuración de variables de entorno para el despliegue en VPS.

--