// Waypure Contact Configuration (dynamic override from window.WAYPURE_CONFIG when available)
export const contactConfig = {
  phone: (typeof window !== 'undefined' && window.WAYPURE_CONFIG && window.WAYPURE_CONFIG.adminPhone) ? window.WAYPURE_CONFIG.adminPhone : '+919061091810',
  whatsapp: (typeof window !== 'undefined' && window.WAYPURE_CONFIG && window.WAYPURE_CONFIG.adminWhatsapp) ? window.WAYPURE_CONFIG.adminWhatsapp : '+919895301810',
  message: 'Hello Waypure! I would like to know more about your products and services.',
};
