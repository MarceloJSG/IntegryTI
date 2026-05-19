// Siempre iniciar arriba al recargar la página
  if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
  }

  window.addEventListener('load', () => {
    window.scrollTo(0, 0);

    // Opcional: elimina el #contacto, #servicios, etc. de la URL
    if (window.location.hash) {
      history.replaceState(null, null, window.location.pathname);
    }
  });
    const loader = document.getElementById('loader');
    const navbar = document.getElementById('navbar');
    const mobileBtn = document.getElementById('mobileBtn');
    const navLinks = document.getElementById('navLinks');
    const form = document.getElementById('contactForm');
    const popup = document.getElementById('popup');

    // Loader
    window.addEventListener('load', () => {
      setTimeout(() => {
        loader.classList.add('hidden');
      }, 650);
    });

    // Navbar scroll
    window.addEventListener('scroll', () => {
      if (window.scrollY > 40) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });

    // Menú móvil
    mobileBtn.addEventListener('click', () => {
      navLinks.classList.toggle('active');
    });

    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => navLinks.classList.remove('active'));
    });

    // Animaciones reveal
    const revealElements = document.querySelectorAll('.reveal');

    const revealOnScroll = () => {
      revealElements.forEach(element => {
        const windowHeight = window.innerHeight;
        const elementTop = element.getBoundingClientRect().top;
        const revealPoint = 120;

        if (elementTop < windowHeight - revealPoint) {
          element.classList.add('active');
        }
      });
    };

    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll();

    // Formulario usando PHP local/hosting: enviar-correo.php
    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      const submitButton = form.querySelector('button[type="submit"]');
      const originalText = submitButton.innerHTML;

      submitButton.innerHTML = 'Enviando...';
      submitButton.disabled = true;

      const formData = new FormData(form);

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          popup.classList.add('active');
          form.reset();

          const select = form.querySelector('select');
          if (select) {
            select.selectedIndex = 0;
          }
        } else {
          alert(result.message || 'Ocurrió un error al enviar el formulario.');
          if (result.error) {
            console.error('Detalle SMTP/PHP:', result.error);
          }
        }
      } catch (error) {
        alert('No se pudo enviar el formulario. Revisa que estés usando un servidor con PHP y que enviar-correo.php responda JSON.');
        console.error(error);
      } finally {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
      }
    });

    function closePopup() {
      popup.classList.remove('active');
    }

    popup.addEventListener('click', function (e) {
      if (e.target === popup) {
        closePopup();
      }
    });

    /* FAQ */

    document

      .querySelectorAll('.faq-question')

      .forEach(question => {

        question.addEventListener(

          'click',

          () => {

            const item =

              question.parentElement;

            item.classList.toggle(

              'active'

            );

          }

        );

      });
