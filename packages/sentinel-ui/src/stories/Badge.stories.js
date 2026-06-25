/** @type { import('@storybook/html').Meta } */
export default {
  title: 'Components/Badge',
  tags: ['autodocs'],
  render: ({ label, variant }) => {
    const span = document.createElement('span');
    span.textContent = label;
    span.className = `c-badge c-badge--${variant}`;
    return span;
  },
  argTypes: {
    label: { control: 'text' },
    variant: {
      control: { type: 'select' },
      options: ['teal', 'success', 'warning', 'danger', 'neutral'],
    },
  },
};

export const Teal = {
  args: { label: 'Sentinel Teal', variant: 'teal' },
};

export const Success = {
  args: { label: 'Activo', variant: 'success' },
};

export const Warning = {
  args: { label: 'Pendiente', variant: 'warning' },
};

export const Danger = {
  args: { label: 'Incidencia', variant: 'danger' },
};

export const Neutral = {
  args: { label: 'Borrador', variant: 'neutral' },
};
