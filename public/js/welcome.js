// Carrusel de imágenes del Home.
(function () {
    'use strict';

    var track = document.getElementById('carouselSlide');
    var totalSlides = track ? track.children.length : 0;
    var slideIndex = 0;
    var carouselInterval = null;

    function render() {
        if (!track || totalSlides === 0) {
            return;
        }

        track.style.transform = 'translateX(-' + (slideIndex * 100) + '%)';
    }

    // Se expone globalmente porque los botones usan onclick="moveSlide(...)".
    window.moveSlide = function (n) {
        if (totalSlides === 0) {
            return;
        }

        slideIndex += n;

        if (slideIndex >= totalSlides) {
            slideIndex = 0;
        }

        if (slideIndex < 0) {
            slideIndex = totalSlides - 1;
        }

        render();
    };

    function startCarouselAutoPlay() {
        if (totalSlides < 2) {
            return;
        }

        clearInterval(carouselInterval);
        carouselInterval = setInterval(function () {
            window.moveSlide(1);
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!track || totalSlides === 0) {
            return;
        }

        var images = track.querySelectorAll('img');
        var imagesLoaded = 0;

        function checkAllImagesLoaded() {
            imagesLoaded++;

            if (imagesLoaded === images.length) {
                render();
                startCarouselAutoPlay();
            }
        }

        images.forEach(function (image) {
            if (image.complete) {
                checkAllImagesLoaded();
            } else {
                image.addEventListener('load', checkAllImagesLoaded);
                image.addEventListener('error', checkAllImagesLoaded);
            }
        });

        if (images.length === 0 || imagesLoaded === images.length) {
            render();
            startCarouselAutoPlay();
        }

        var carousel = document.querySelector('.px-carousel');

        if (carousel) {
            carousel.addEventListener('mouseenter', function () {
                clearInterval(carouselInterval);
            });

            carousel.addEventListener('mouseleave', function () {
                startCarouselAutoPlay();
            });
        }
    });
}());
