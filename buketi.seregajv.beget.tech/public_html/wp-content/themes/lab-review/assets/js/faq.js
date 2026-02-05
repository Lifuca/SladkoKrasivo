document.addEventListener('DOMContentLoaded', function () {
  const items = document.querySelectorAll('.questions__item');

  items.forEach(item => {
    const head = item.querySelector('.questions__item-head');
    const body = item.querySelector('.questions__item-body');

    head.addEventListener('click', () => {
      const isOpen = item.classList.contains('active');

      items.forEach(i => {
        i.classList.remove('active');
        i.querySelector('.questions__item-body').style.maxHeight = null;
      });

      if (!isOpen) {
        item.classList.add('active');
        body.style.maxHeight = body.scrollHeight + 'px';
      }
    });
  });
});
