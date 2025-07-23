document.addEventListener('DOMContentLoaded', () => {
    const faqQuestions = document.querySelectorAll('.faq-question');

    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const currentAnswer = question.nextElementSibling;
            const currentIcon = question.querySelector('.faq-icon');

            // Cierra todas las otras respuestas abiertas
            faqQuestions.forEach(otherQuestion => {
                const otherAnswer = otherQuestion.nextElementSibling;
                const otherIcon = otherQuestion.querySelector('.faq-icon');

                if (otherQuestion !== question && otherQuestion.classList.contains('active')) {
                    otherQuestion.classList.remove('active');
                    otherAnswer.classList.remove('active');
                    otherIcon.textContent = '+'; // Restaura el icono a '+'
                }
            });

            // Alterna la clase 'active' en la pregunta y la respuesta actual
            question.classList.toggle('active');
            currentAnswer.classList.toggle('active');

            // Cambia el signo del icono
            if (question.classList.contains('active')) {
                currentIcon.textContent = '–'; // Cambiar a signo de resta
            } else {
                currentIcon.textContent = '+'; // Cambiar a signo de suma
            }
        });
    });
});