/**
 * Carrusel de videos de la página de demo.
 *
 * Solo carga el video activo: los iframes de YouTube y los archivos se
 * cargan al entrar en la diapositiva, y se pausan al salir.
 */
document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('[data-video-carousel]');

    if (!root) {
        return;
    }

    var slides = Array.prototype.slice.call(root.querySelectorAll('[data-video-slide]'));
    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-video-dot]'));
    var thumbs = Array.prototype.slice.call(root.querySelectorAll('[data-video-thumb]'));
    var prev = root.querySelector('[data-video-prev]');
    var next = root.querySelector('[data-video-next]');
    var title = root.querySelector('[data-video-title]');
    var description = root.querySelector('[data-video-description]');
    var counter = root.querySelector('[data-video-counter]');

    if (!slides.length) {
        return;
    }

    var current = 0;

    function loadSlide(slide) {
        var iframe = slide.querySelector('iframe');
        var video = slide.querySelector('video');

        if (iframe) {
            var iframeSrc = iframe.getAttribute('data-src');

            if (iframeSrc && (!iframe.getAttribute('src') || iframe.getAttribute('src') === 'about:blank')) {
                iframe.setAttribute('src', iframeSrc);
            }
        }

        if (video) {
            var videoSrc = video.getAttribute('data-src');

            if (videoSrc && !video.getAttribute('src')) {
                video.setAttribute('src', videoSrc);
                video.load();
            }
        }
    }

    function unloadSlide(slide) {
        var iframe = slide.querySelector('iframe');
        var video = slide.querySelector('video');

        if (iframe && iframe.getAttribute('src') && iframe.getAttribute('src') !== 'about:blank') {
            iframe.setAttribute('src', 'about:blank');
        }

        if (video) {
            try {
                video.pause();
                video.currentTime = 0;
            } catch (error) {
                // Algunos navegadores bloquean currentTime antes de cargar metadata.
            }
        }
    }

    function activate(index) {
        if (!slides.length) {
            return;
        }

        current = (index + slides.length) % slides.length;

        slides.forEach(function (slide, slideIndex) {
            var active = slideIndex === current;

            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');

            if (active) {
                loadSlide(slide);
            } else {
                unloadSlide(slide);
            }
        });

        dots.forEach(function (dot, dotIndex) {
            var active = dotIndex === current;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        thumbs.forEach(function (thumb, thumbIndex) {
            thumb.classList.toggle('is-active', thumbIndex === current);
            thumb.setAttribute('aria-current', thumbIndex === current ? 'true' : 'false');
        });

        var activeSlide = slides[current];

        if (title) {
            title.textContent = activeSlide.getAttribute('data-title') || '';
        }

        if (description) {
            var text = activeSlide.getAttribute('data-description') || '';
            description.textContent = text;
            description.hidden = text === '';
        }

        if (counter) {
            counter.textContent = (current + 1) + ' / ' + slides.length;
        }

        root.setAttribute('data-current', String(current));
    }

    if (prev) {
        prev.addEventListener('click', function () {
            activate(current - 1);
        });
    }

    if (next) {
        next.addEventListener('click', function () {
            activate(current + 1);
        });
    }

    dots.forEach(function (dot, index) {
        dot.addEventListener('click', function () {
            activate(index);
        });
    });

    thumbs.forEach(function (thumb, index) {
        thumb.addEventListener('click', function () {
            activate(index);
        });
    });

    // Flechas del teclado cuando el foco está dentro del carrusel.
    root.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            activate(current - 1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            activate(current + 1);
        }
    });

    // Deslizar en móvil.
    var startX = null;
    var stage = root.querySelector('.px-video-carousel__stage');

    if (stage) {
        stage.addEventListener('touchstart', function (event) {
            startX = event.changedTouches[0].clientX;
        }, { passive: true });

        stage.addEventListener('touchend', function (event) {
            if (startX === null) {
                return;
            }

            var endX = event.changedTouches[0].clientX;
            var diff = endX - startX;
            startX = null;

            if (Math.abs(diff) < 45) {
                return;
            }

            activate(diff > 0 ? current - 1 : current + 1);
        }, { passive: true });
    }

    activate(0);
});
