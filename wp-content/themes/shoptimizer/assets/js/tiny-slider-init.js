document.addEventListener('DOMContentLoaded', function() {
    var sliderContainer = document.querySelector('.pdp-complementary--container');
    var slides = sliderContainer ? sliderContainer.children : [];
    if (slides.length > 0) {
        var slider = tns({
            container: '.pdp-complementary--container',
            items: 1,
            slideBy: 1,
            speed: 300,
            nav: false,
            controls: true,
            controlsContainer: '.pdp-complementary--nav',
            prevButton: '.pdp-complementary--nav-prev',
            nextButton: '.pdp-complementary--nav-next',
            rewind: false,
            autoplay: false,
            mouseDrag: true,
            autoplayButtonOutput: false,
            loop: false,       
        });
    }
});