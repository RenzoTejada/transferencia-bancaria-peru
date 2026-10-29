(function () {
    'use strict';
    if (!window.wc || !window.wc.wcBlocksRegistry || !window.wc.wcSettings || !window.wp || !window.wp.element) { return; }
    const data = window.wc.wcSettings.getSetting('transfer-peru_data', {});
    const el = window.wp.element.createElement;
    const decode = window.wp.htmlEntities && window.wp.htmlEntities.decodeEntities ? window.wp.htmlEntities.decodeEntities : (s) => s;
    const title = decode(data.title || 'Transferencia bancaria Perú');
    const Content = () => el('div', {className: 'rt-transfer-peru-blocks'}, data.description ? decode(data.description) : '');
    window.wc.wcBlocksRegistry.registerPaymentMethod({
       name: 'transfer-peru', label: title, ariaLabel: title,
       content: el(Content), edit: el(Content), canMakePayment: () => true,
       supports: {features: data.supports || ['products']}
    });
})();
