export const site = {
  name: "Peluquería",
  descriptor: "Peluquería · Barbería",
  city: "Tu ciudad",
  addressLine1: "Calle Principal, 00",
  postalAndCity: "00000 Tu ciudad",
  phoneDisplay: "600 00 00 00",
  phoneHref: "tel:+34600000000",
  mapsHref: "https://www.google.com/maps/search/?api=1&query=peluqueria+barberia",
  social: {
    instagram: import.meta.env.VITE_INSTAGRAM_URL || "https://www.instagram.com/",
    x: import.meta.env.VITE_X_URL || "https://x.com/",
    facebook: import.meta.env.VITE_FACEBOOK_URL || "https://www.facebook.com/",
    tiktok: import.meta.env.VITE_TIKTOK_URL || "https://www.tiktok.com/",
    whatsapp: import.meta.env.VITE_WHATSAPP_URL || "https://wa.me/34600000000",
  },
} as const;
