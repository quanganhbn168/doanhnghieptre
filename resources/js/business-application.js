const select = document.querySelector('[data-industry-picker]');

if (select && window.TomSelect) {
    const counter = document.getElementById('industry-count');
    const selectedIds = JSON.parse(select.dataset.selected);
    const availableIds = new Set(Array.from(select.options, (option) => option.value));

    const picker = new window.TomSelect(select, {
        items: selectedIds.filter((id) => availableIds.has(id)),
        maxItems: 5,
        create: false,
        copyClassesToDropdown: false,
        clearAfterSelect: true,
        hideSelected: true,
        placeholder: 'Tìm và chọn khối ngành nghề…',
        plugins: {
            remove_button: {
                html() {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'remove';
                    button.textContent = '×';
                    return button;
                },
            },
        },
        render: {
            item(data, escape) {
                return `<div><span>${escape(data.text)}</span></div>`;
            },
            no_results() {
                return '<div class="no-results">Không tìm thấy khối ngành nghề phù hợp.</div>';
            },
        },
        onItemAdd(value, item) {
            const button = item.querySelector('.remove');
            const label = `Bỏ ${this.options[value].text}`;
            button.setAttribute('aria-label', label);
            button.title = label;
        },
        onItemRemove() {
            this.focus();
        },
    });

    select.setAttribute('aria-hidden', 'true');
    picker.control_input.setAttribute('aria-describedby', select.getAttribute('aria-describedby'));
    picker.control_input.setAttribute('aria-required', 'true');
    picker.control_input.setAttribute('aria-invalid', select.getAttribute('aria-invalid'));

    const updateSelection = () => {
        const count = picker.items.length;
        counter.textContent = count === 5 ? 'Đã đủ 5 khối. Bỏ một khối để chọn khối khác.' : `Đã chọn ${count}/5 khối`;
        picker.settings.placeholder = count === 5 ? 'Đã chọn đủ 5 khối' : (count ? 'Chọn thêm khối…' : 'Tìm và chọn khối ngành nghề…');
        picker.control_input.readOnly = count === 5;
        picker.inputState();
    };

    picker.on('change', () => {
        picker.control_input.setAttribute('aria-invalid', 'false');
        updateSelection();
    });

    // Keep native required validation, but focus the visible searchable control.
    select.addEventListener('invalid', (event) => {
        event.preventDefault();
        picker.control_input.setAttribute('aria-invalid', 'true');
        counter.textContent = 'Vui lòng chọn ít nhất một khối ngành nghề.';
        picker.focus();
    });

    updateSelection();
}
