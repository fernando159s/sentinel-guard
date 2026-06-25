/** @type { import('@storybook/html').Meta } */
export default {
  title: 'Design Tokens/Colors',
  tags: ['autodocs'],
  render: () => {
    const tealShades = [
      ['teal-50', '#e6f7f7'],
      ['teal-100', '#b3e8e8'],
      ['teal-200', '#80d8d8'],
      ['teal-300', '#4dc9c9'],
      ['teal-400', '#26bfbf'],
      ['teal-500', '#00b5b5'],
      ['teal-600', '#00a3a3'],
      ['teal-700', '#008c8c'],
      ['teal-800', '#007575'],
      ['teal-900', '#005757'],
    ];

    const wrap = document.createElement('div');
    wrap.style.cssText = 'display:flex;flex-direction:column;gap:8px;font-family:system-ui;';

    const title = document.createElement('h3');
    title.textContent = 'Sentinel Teal Palette';
    title.style.cssText = 'margin-bottom:12px;font-size:14px;font-weight:600;';
    wrap.appendChild(title);

    const grid = document.createElement('div');
    grid.style.cssText = 'display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;';

    tealShades.forEach(([name, hex]) => {
      const swatch = document.createElement('div');
      swatch.style.cssText = `background:${hex};border-radius:6px;padding:12px 8px;`;
      const label = document.createElement('small');
      label.style.cssText = `display:block;font-size:11px;font-weight:600;color:${name.includes('50') || name.includes('100') || name.includes('200') ? '#374151' : '#fff'};`;
      label.textContent = `$color-${name}`;
      const value = document.createElement('small');
      value.style.cssText = label.style.cssText;
      value.textContent = hex;
      swatch.appendChild(label);
      swatch.appendChild(value);
      grid.appendChild(swatch);
    });

    wrap.appendChild(grid);
    return wrap;
  },
};

export const SentinelTealPalette = {};
