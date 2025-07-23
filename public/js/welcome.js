let slideIndex = 0;
const slides = document.getElementById('carouselSlide').children;
const totalSlides = slides.length;

function moveSlide(n) {
    slideIndex += n;
    if (slideIndex >= totalSlides) {
        slideIndex = 0;
    }
    if (slideIndex < 0) {
        slideIndex = totalSlides - 1;
    }
    // Usa transform para un movimiento más suave
    document.getElementById('carouselSlide').style.transform = `translateX(-${slideIndex * 100}%)`;
}

// Inicializa el carrusel
document.addEventListener('DOMContentLoaded', () => {
    // Asegura que todas las imágenes estén cargadas antes de calcular el ancho
    const carouselSlide = document.getElementById('carouselSlide');
    const images = carouselSlide.querySelectorAll('img');
    let imagesLoaded = 0;

    const checkAllImagesLoaded = () => {
        imagesLoaded++;
        if (imagesLoaded === images.length) {
            // Todas las imágenes cargadas, inicializa el carrusel
            moveSlide(0); // Asegura la posición inicial correcta
            startCarouselAutoPlay();
        }
    };

    images.forEach(img => {
        if (img.complete) {
            checkAllImagesLoaded();
        } else {
            img.addEventListener('load', checkAllImagesLoaded);
            img.addEventListener('error', () => {
                // Maneja errores de carga de imagen para no bloquear el carrusel
                console.error("Error al cargar la imagen:", img.src);
                checkAllImagesLoaded(); // Considera la imagen como "cargada" para no detener el carrusel
            });
        }
    });

    // Si no hay imágenes o si ya estaban todas cargadas (ej. caché del navegador)
    if (images.length === 0 || imagesLoaded === images.length) {
        moveSlide(0);
        startCarouselAutoPlay();
    }
});

// Autoplay del carrusel
let carouselInterval;

function startCarouselAutoPlay() {
    // Limpia cualquier intervalo existente antes de iniciar uno nuevo
    clearInterval(carouselInterval);
    carouselInterval = setInterval(() => {
        moveSlide(1);
    }, 5000); // Cambia de slide cada 5 segundos
}

// Pausa el carrusel al pasar el mouse por encima
document.querySelector('.carousel-container').addEventListener('mouseenter', () => {
    clearInterval(carouselInterval);
});

// Reanuda el carrusel al quitar el mouse
document.querySelector('.carousel-container').addEventListener('mouseleave', () => {
    startCarouselAutoPlay();
});