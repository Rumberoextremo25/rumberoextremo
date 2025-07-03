document.addEventListener("DOMContentLoaded", () => {
    console.log("admin-dashboard.js cargado.");

    // --- 1. Lógica para activar enlaces del Sidebar (si no se maneja 100% con Blade) ---
    // Esta parte es más una redundancia si ya usas `request()->routeIs()`,
    // pero puede ser útil si tu lógica de rutas activas es más compleja o dinámica.
    const sidebarLinks = document.querySelectorAll(".sidebar ul li a");

    sidebarLinks.forEach((link) => {
        // Elimina la clase 'active' de todos los enlaces primero
        link.classList.remove("active");

        // Lógica para determinar el enlace activo (ej. basada en la URL actual)
        // En un entorno de Laravel, `request()->routeIs()` es mejor,
        // pero este es un ejemplo de cómo podrías hacerlo con JS puro si fuera necesario.
        const currentPath = window.location.pathname;
        const linkPath = new URL(link.href).pathname;

        if (currentPath === linkPath) {
            link.classList.add("active");
        }
    });

    // --- 2. Animación de entrada para los KPI Cards ---
    const kpiCards = document.querySelectorAll(".kpi-card");
    kpiCards.forEach((card, index) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        setTimeout(() => {
            card.style.transition =
                "opacity 0.6s ease-out, transform 0.6s ease-out";
            card.style.opacity = "1";
            card.style.transform = "translateY(0)";
        }, 100 * index); // Pequeño retraso para un efecto escalonado
    });

    // --- 3. Ejemplo de Gráfico con Chart.js ---
    // Asegúrate de tener un <canvas id="myChart"></canvas> en tu admin/dashboard.blade.php
    const ctx = document.getElementById("usersChart"); // ID de tu elemento canvas

    if (ctx) {
        // Asegúrate de que el canvas exista en la página
        new Chart(ctx, {
            type: "line", // Tipo de gráfico: 'bar', 'line', 'pie', 'doughnut', etc.
            data: {
                labels: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul"],
                datasets: [
                    {
                        label: "Usuarios Registrados (Mensual)",
                        data: [65, 59, 80, 81, 56, 55, 40],
                        backgroundColor: "rgba(255, 75, 75, 0.2)", // Color de fondo (transparente)
                        borderColor: "#FF4B4B", // Color de la línea
                        borderWidth: 2,
                        tension: 0.3, // Suaviza la línea
                        fill: true, // Rellenar área bajo la línea
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Permite que el gráfico se ajuste a su contenedor
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Número de Usuarios",
                        },
                    },
                    x: {
                        title: {
                            display: true,
                            text: "Mes",
                        },
                    },
                },
                plugins: {
                    title: {
                        display: true,
                        text: "Tendencia de Usuarios Registrados",
                        font: {
                            size: 16,
                        },
                    },
                    legend: {
                        display: false, // Oculta la leyenda si solo hay un dataset
                    },
                },
            },
        });
    }

    const ctxBar = document.getElementById("productsChart");
    if (ctxBar) {
        new Chart(ctxBar, {
            type: "bar", // Gráfico de barras
            data: {
                labels: ["Electrónica", "Ropa", "Comida", "Servicios", "Otros"],
                datasets: [
                    {
                        label: "Productos por Categoría",
                        data: [120, 190, 30, 50, 20],
                        backgroundColor: [
                            "rgba(255, 75, 75, 0.8)",
                            "rgba(54, 162, 235, 0.8)",
                            "rgba(255, 206, 86, 0.8)",
                            "rgba(75, 192, 192, 0.8)",
                            "rgba(153, 102, 255, 0.8)",
                        ],
                        borderColor: [
                            "#FF4B4B",
                            "#36A2EB",
                            "#FFCE56",
                            "#4BC0C0",
                            "#9966FF",
                        ],
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Cantidad de Productos",
                        },
                    },
                },
                plugins: {
                    title: {
                        display: true,
                        text: "Distribución de Productos por Categoría",
                        font: {
                            size: 16,
                        },
                    },
                    legend: {
                        display: false,
                    },
                },
            },
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        // --- Funcionalidad del Modo Oscuro/Claro ---
        const body = document.body;
        const darkModeToggle = document.getElementById("darkModeToggle");

        // Cargar la preferencia del usuario desde localStorage al iniciar
        const savedTheme = localStorage.getItem("theme");
        if (savedTheme === "dark-mode") {
            body.classList.add("dark-mode");
            // Si el toggle existe, actualiza su estado
            if (darkModeToggle) {
                darkModeToggle.checked = true;
            }
        } else {
            // Si no hay preferencia o es 'light-mode', asegurarse de que no esté en modo oscuro
            body.classList.remove("dark-mode");
            if (darkModeToggle) {
                darkModeToggle.checked = false;
            }
        }

        // Si el toggle existe y se le da click, cambia el modo y guarda la preferencia
        if (darkModeToggle) {
            darkModeToggle.addEventListener("change", () => {
                if (darkModeToggle.checked) {
                    body.classList.add("dark-mode");
                    localStorage.setItem("theme", "dark-mode"); // Guardar preferencia
                } else {
                    body.classList.remove("dark-mode");
                    localStorage.setItem("theme", "light-mode"); // Guardar preferencia
                }
            });
        }

        // --- Lógica para resaltar el enlace activo del sidebar ---
        const currentPath = window.location.pathname;
        document.querySelectorAll(".sidebar ul li a").forEach((link) => {
            // Remueve la clase 'active' de todos primero
            link.classList.remove("active");

            // Agrega la clase 'active' si la ruta actual coincide (o contiene) el href del enlace
            // Esto es más robusto que solo por texto
            const linkHref = new URL(link.href).pathname;
            if (
                currentPath === linkHref ||
                (currentPath.startsWith(linkHref) && linkHref !== "/")
            ) {
                link.classList.add("active");
            }
        });
    });

    // SCRIPTS DE REPORTES
    
});