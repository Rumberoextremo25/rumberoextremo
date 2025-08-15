// Inicializa el índice de la diapositiva actual.
let slideIndex = 0;
// Obtiene todos los elementos hijos (las imágenes) del carrusel.
const slides = document.getElementById('carouselSlide').children;
// Determina el número total de diapositivas de forma dinámica.
const totalSlides = slides.length;

/**
 * Mueve el carrusel a la diapositiva siguiente o anterior.
 * @param {number} n - El número de diapositivas a mover. Usar 1 para siguiente y -1 para anterior.
 */
function moveSlide(n) {
    // Actualiza el índice de la diapositiva.
    slideIndex += n;
    // Si el índice supera el número total de diapositivas, se reinicia a 0.
    if (slideIndex >= totalSlides) {
        slideIndex = 0;
    }
    // Si el índice es negativo, se ajusta a la última diapositiva.
    if (slideIndex < 0) {
        slideIndex = totalSlides - 1;
    }
    // Usa una transformación CSS para un movimiento más suave del carrusel.
    document.getElementById('carouselSlide').style.transform = `translateX(-${slideIndex * 100}%)`;
}

// Inicializa el carrusel una vez que el DOM esté completamente cargado.
document.addEventListener('DOMContentLoaded', () => {
    // Selecciona todas las imágenes dentro del carrusel.
    const carouselSlide = document.getElementById('carouselSlide');
    const images = carouselSlide.querySelectorAll('img');
    let imagesLoaded = 0;

    // Función para verificar si todas las imágenes se han cargado.
    const checkAllImagesLoaded = () => {
        imagesLoaded++;
        if (imagesLoaded === images.length) {
            // Si todas las imágenes están cargadas, inicializa el carrusel.
            moveSlide(0); // Asegura la posición inicial correcta.
            startCarouselAutoPlay();
        }
    };

    // Itera sobre cada imagen para agregar oyentes de eventos.
    images.forEach(img => {
        if (img.complete) {
            // Si la imagen ya está en caché, la cuenta como cargada.
            checkAllImagesLoaded();
        } else {
            // Escucha el evento 'load' para saber cuándo la imagen ha terminado de cargar.
            img.addEventListener('load', checkAllImagesLoaded);
            // Maneja posibles errores de carga.
            img.addEventListener('error', () => {
                console.error("Error al cargar la imagen:", img.src);
                checkAllImagesLoaded(); // Continúa el proceso incluso si hay un error.
            });
        }
    });

    // Si no hay imágenes o ya estaban todas cargadas, inicia el carrusel.
    if (images.length === 0 || imagesLoaded === images.length) {
        moveSlide(0);
        startCarouselAutoPlay();
    }
});

// Autoplay del carrusel.
let carouselInterval;

/**
 * Inicia la reproducción automática del carrusel.
 */
function startCarouselAutoPlay() {
    // Limpia cualquier intervalo existente para evitar duplicados.
    clearInterval(carouselInterval);
    carouselInterval = setInterval(() => {
        moveSlide(1); // Mueve a la siguiente diapositiva.
    }, 5000); // Cambia de slide cada 5 segundos.
}

// Pausa el carrusel cuando el mouse entra en el contenedor.
document.querySelector('.carousel-container').addEventListener('mouseenter', () => {
    clearInterval(carouselInterval);
});

// Reanuda el carrusel cuando el mouse sale del contenedor.
document.querySelector('.carousel-container').addEventListener('mouseleave', () => {
    startCarouselAutoPlay();
});