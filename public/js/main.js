// public/js/main.js

document.addEventListener('DOMContentLoaded', function () {
    
    // --- Lógica del Sidebar (Rescatada de footer.php) ---
    const sidebar = document.getElementById('sidebar'); // (Asegúrate de que tu header.php tenga este ID)
    const sidebarToggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('overlay'); // (Asegúrate de que tu header.php tenga este ID)
    const breakpointMd = 768; // Bootstrap 'md'

    if (sidebarToggle && sidebar && overlay) {
        
        const closeSidebar = () => {
            sidebar.classList.remove('active');
            overlay.classList.add('d-none');
            overlay.classList.remove('show');
        };

        const openSidebar = () => {
            sidebar.classList.add('active');
            overlay.classList.remove('d-none');
            overlay.classList.add('show');
        };

        sidebarToggle.addEventListener('click', (e) => {
            e.preventDefault();
            if (sidebar.classList.contains('active')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
        
        overlay.addEventListener('click', () => {
             closeSidebar();
        });

        // Cierra en pantallas pequeñas al cargar
        if (window.innerWidth < breakpointMd) {
             closeSidebar();
        }

        // Cierra al hacer clic en un enlace (excepto desplegables)
        document.querySelectorAll('#sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (link.getAttribute('data-bs-toggle') !== 'collapse') {
                    if (window.innerWidth < breakpointMd) {
                        closeSidebar();
                    }
                }
            });
        });
    }

    // --- Fin Lógica Sidebar ---

    // Puedes añadir más JS global aquí
    
});
