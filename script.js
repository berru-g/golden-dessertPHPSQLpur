//loader img smooth
window.addEventListener('load', () => {
    const loader = document.querySelector('.loader');
    setTimeout(() => loader.classList.add('fade-out'), 500);
});

//menu
const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');
const links = document.querySelectorAll('.nav-links a');

hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('nav-active');
    hamburger.classList.toggle('toggle');
});

links.forEach(link => {
    link.addEventListener('click', function () {
        navLinks.classList.remove('nav-active');
        hamburger.classList.remove('toggle');
    });
});

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
        e.preventDefault();
        document.querySelector(anchor.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
    });
});


// animation scroll via GASP
gsap.registerPlugin(ScrollTrigger);

gsap.utils.toArray("[data-gsap]").forEach(elem => {
    gsap.fromTo(elem,
        { opacity: 0, y: 40 },
        {
            opacity: 1,
            y: 0,
            duration: 1.2,
            ease: "power2.out",
            scrollTrigger: {
                trigger: elem,
                start: "top 80%",
                toggleActions: "play none none none"
            }
        }
    );
});