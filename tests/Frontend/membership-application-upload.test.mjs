import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/business-application.js', import.meta.url), 'utf8');

function uploadField() {
    const listeners = {};
    const attributes = {};
    const error = { textContent: '', hidden: true };
    const input = {
        accept: '.pdf,.doc,.docx',
        dataset: { maxFileSize: '10485760' },
        files: [],
        validity: { valueMissing: false },
        validationMessage: '',
        addEventListener: (event, listener) => { listeners[event] = listener; },
        setAttribute: (name, value) => { attributes[name] = value; },
        setCustomValidity(message) { this.validationMessage = message; },
    };
    const document = {
        querySelector: () => null,
        getElementById: (id) => id === 'membership_application' ? input : error,
    };
    vm.runInNewContext(source, { document });
    return { input, error, attributes, listeners };
}

test('PDF and Word extensions, including uppercase, accept files up to 10 MB', () => {
    for (const name of ['don.pdf', 'don.PDF', 'don.doc', 'don.docx']) {
        const { input, error, listeners } = uploadField();
        input.files = [{ name, size: 10485760 }];
        listeners.change();
        assert.equal(input.validationMessage, '');
        assert.equal(error.hidden, true);
    }
});

test('disallowed extensions and oversized files block submission with an inline error', () => {
    for (const file of [{ name: 'don.png', size: 100 }, { name: 'don.pdf.exe', size: 100 }, { name: 'don.zip', size: 100 }, { name: 'don.docx', size: 10485761 }]) {
        const { input, error, attributes, listeners } = uploadField();
        input.files = [file];
        listeners.change();
        assert.notEqual(input.validationMessage, '');
        assert.equal(error.hidden, false);
        assert.equal(attributes['aria-invalid'], 'true');
    }
});

test('choosing a valid replacement or clearing an optional file clears the previous error', () => {
    const { input, error, attributes, listeners } = uploadField();
    for (const files of [[{ name: 'don.pdf', size: 100 }], []]) {
        input.files = [{ name: 'don.png', size: 100 }];
        listeners.change();
        input.files = files;
        listeners.change();
        assert.equal(input.validationMessage, '');
        assert.equal(error.hidden, true);
        assert.equal(attributes['aria-invalid'], 'false');
    }
});

test('a missing required application gets an inline Vietnamese message', () => {
    const { input, error, listeners } = uploadField();
    input.validity.valueMissing = true;
    listeners.invalid();
    assert.match(error.textContent, /cần tải lên bản đơn/);
    assert.equal(error.hidden, false);
});
