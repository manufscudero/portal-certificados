# Portal de Certificados Digitales - Muestra Nacional de Epidemiología

Sistema web desarrollado en PHP y Docker para la consulta y generación en tiempo real de certificados digitales en formato PDF para participantes de la Muestra Nacional de Epidemiología (DGVS).

---

# Características

- **Consulta rápida por C.I.**: Búsqueda simplificada por número de Cédula de Identidad con filtrado opcional por edición/muestra.
- **Generación dinámica de PDF**: Generación al instante de certificados utilizando la librería **FPDF**.
- **Panel de Administración**: Gestión de plantillas de certificados y roles asociados.
- **Arquitectura basada en Docker**: Entorno de desarrollo y producción containerizado con PHP 8.2 (Apache) y MySQL 8.0.
- **Diseño Moderno y Responsivo**: Interfaz fluida basada en Bootstrap 5, icons y estilos personalizados.

---

# Tecnologías Utilizadas

- **Lenguaje**: PHP 8.2 (PDO para conexiones seguras a BD)
- **Base de Datos**: MySQL 8.0
- **Librería PDF**: FPDF
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5, Bootstrap Icons
- **Infraestructura**: Docker & Docker Compose

---

# Estructura del Proyecto


.
├── admin/                     # Panel de administración (subida y gestión de plantillas)
│   ├── guardar_plantilla.php
│   └── index.php
├── assets/                    # Hojas de estilo y recursos estáticos
│   └── css/
│       └── estilos.css
├── config/                    # Configuración de base de datos
│   ├── db.php.example         # Plantilla de configuración
│   └── db.php                 # (Ignorado en git) Configuración local
├── lib/                       # Librería FPDF y fuentes
│   ├── font/
│   └── fpdf.php
├── Dockerfile                 # Configuración de imagen PHP/Apache
├── docker-compose.yml         # Orquestación de servicios (PHP + MySQL)
├── generar_pdf.php            # Script de renderizado y descarga de certificados PDF
├── index.php                  # Portal público de consulta
└── README.md

Requisitos Previos
Docker y Docker Compose instalados.

Instalación y Despliegue
Clonar el repositorio:

Bash
git clone [https://github.com/TU_USUARIO/NOMBRE_REPO.git](https://github.com/TU_USUARIO/NOMBRE_REPO.git)
cd NOMBRE_REPO
Configurar la conexión a la Base de Datos:
Copia el archivo de ejemplo para crear tu configuración local:

Bash
cp config/db.php.example config/db.php
Edita config/db.php con las credenciales de tu contenedor o servidor de base de datos.

Iniciar el entorno con Docker Compose:

Bash
docker-compose up -d --build
Acceder a la aplicación:

Portal Público: http://localhost:8080

Panel Admin: http://localhost:8080/admin

Licencia y Créditos
Desarrollado para la Dirección General de Vigilancia de la Salud (DGVS) / Unidad de Tecnología de la Información y Comunicación.
