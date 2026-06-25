/** @type { import('@storybook/html').Meta } */
export default {
  title: 'Components/Button',
  tags: ['autodocs'],
  render: ({ label, variant, size, disabled }) => {
    const btn = document.createElement('button');
    btn.textContent = label;
    btn.className = ['c-btn', `c-btn--${variant}`, size ? `c-btn--${size}` : ''].filter(Boolean).join(' ');
    if (disabled) {
      btn.disabled = true;
    }
    return btn;
  },
  argTypes: {
    label: { control: 'text' },
    variant: {
      control: { type: 'select' },
      options: ['primary', 'secondary', 'ghost', 'danger'],
    },
    size: {
      control: { type: 'select' },
      options: ['sm', '', 'lg'],
    },
    disabled: { control: 'boolean' },
  },
};

export const Primary = {
  args: { label: 'Guardar cambios', variant: 'primary', size: '', disabled: false },
};

export const Secondary = {
  args: { label: 'Cancelar', variant: 'secondary', size: '', disabled: false },
};

export const Ghost = {
  args: { label: 'Ver detalle', variant: 'ghost', size: '', disabled: false },
};

export const Danger = {
  args: { label: 'Eliminar registro', variant: 'danger', size: '', disabled: false },
};

export const Small = {
  args: { label: 'Acción', variant: 'primary', size: 'sm', disabled: false },
};

export const Large = {
  args: { label: 'Acción principal', variant: 'primary', size: 'lg', disabled: false },
};

export const Disabled = {
  args: { label: 'No disponible', variant: 'primary', size: '', disabled: true },
};
