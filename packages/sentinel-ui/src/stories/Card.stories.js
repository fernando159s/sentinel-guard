/** @type { import('@storybook/html').Meta } */
export default {
  title: 'Components/Card',
  tags: ['autodocs'],
  render: ({ title, body, withHeader, withFooter, hoverable }) => {
    const card = document.createElement('div');
    card.className = ['c-card', hoverable ? 'c-card--hover' : ''].filter(Boolean).join(' ');
    card.style.maxWidth = '400px';

    if (withHeader) {
      const header = document.createElement('div');
      header.className = 'c-card__header';
      header.innerHTML = `<strong>${title}</strong>`;
      card.appendChild(header);
    }

    const bodyEl = document.createElement('div');
    bodyEl.className = 'c-card__body';
    bodyEl.textContent = body;
    card.appendChild(bodyEl);

    if (withFooter) {
      const footer = document.createElement('div');
      footer.className = 'c-card__footer';
      footer.innerHTML = '<small>Última actualización: 25/06/2026</small>';
      card.appendChild(footer);
    }

    return card;
  },
  argTypes: {
    title:      { control: 'text' },
    body:       { control: 'text' },
    withHeader: { control: 'boolean' },
    withFooter: { control: 'boolean' },
    hoverable:  { control: 'boolean' },
  },
};

export const Default = {
  args: {
    title: 'Registro PSC',
    body: 'Contenido del registro de seguridad de la información.',
    withHeader: true,
    withFooter: false,
    hoverable: false,
  },
};

export const WithFooter = {
  args: {
    title: 'Registro PSC',
    body: 'Contenido del registro de seguridad de la información.',
    withHeader: true,
    withFooter: true,
    hoverable: false,
  },
};

export const Hoverable = {
  args: {
    title: 'Empresa activa',
    body: 'Haz hover sobre esta tarjeta.',
    withHeader: true,
    withFooter: false,
    hoverable: true,
  },
};
