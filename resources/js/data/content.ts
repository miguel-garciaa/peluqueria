import { Brush, Droplets, Gem, Palette, Scissors, Sparkles } from "lucide-react";
import heroImageColor from "@/assets/hero-salon-color.webp";
import type { GalleryItem, Professional, Service, Testimonial, Transformation } from "@/types";

const image = (id: string, width = 1200) =>
  `https://images.unsplash.com/${id}?auto=format&fit=crop&w=${width}&q=84`;

export const heroImage = heroImageColor;

export const services: Service[] = [
  { id: "cut", title: "Corte & Peinado", description: "Un corte diseñado para tu textura, facciones y forma de vivir.", longDescription: "Comenzamos con un diagnóstico de textura, caída y rutina. Diseñamos una forma que favorece tus facciones y que puedas mantener en casa sin depender de un peinado complicado.", benefits: ["Diagnóstico y asesoramiento", "Lavado y corte personalizado", "Peinado y pautas de mantenimiento"], imageSrc: image("photo-1595476108010-b4d1f102b1b1", 1400), imageAlt: "Estilista diseñando un corte personalizado", priceFrom: 35, duration: "45 min", icon: Scissors },
  { id: "balayage", title: "Balayage", description: "Luz y profundidad aplicadas a medida, con un acabado natural.", longDescription: "Estudiamos tu tono de piel, base natural y mantenimiento deseado para colocar la luz donde más favorece. El resultado crece de forma suave y conserva profundidad en la raíz.", benefits: ["Diseño de color personalizado", "Matiz y protección del cabello", "Acabado con brillo y movimiento"], imageSrc: image("photo-1560869713-7d0a29430803", 1400), imageAlt: "Trabajo de color y balayage realizado en el salón", priceFrom: 90, duration: "150 min", icon: Palette },
  { id: "keratin", title: "Keratina & Brillo", description: "Control del encrespamiento y tacto sedoso sin perder movimiento.", longDescription: "Tratamiento adaptado al estado y porosidad de tu cabello. Reduce el encrespamiento, mejora el tacto y facilita el peinado manteniendo un resultado flexible y natural.", benefits: ["Diagnóstico de porosidad", "Tratamiento ajustado a tu textura", "Sellado y acabado luminoso"], imageSrc: image("photo-1616683693504-3ea7e9ad6fec", 1400), imageAlt: "Cabello brillante tras un tratamiento de keratina", priceFrom: 75, duration: "120 min", icon: Sparkles },
  { id: "barber", title: "Barba & Estilo", description: "Perfilado preciso, toalla caliente y cuidado de la piel.", longDescription: "Definimos barba, contornos y proporciones en armonía con tu corte. La preparación con toalla caliente suaviza el vello y convierte el afeitado en un ritual cómodo y preciso.", benefits: ["Diseño según facciones", "Perfilado y toalla caliente", "Hidratación de piel y barba"], imageSrc: image("photo-1622286342621-4bd786c2447c", 1400), imageAlt: "Barbero perfilando un corte y una barba", priceFrom: 22, duration: "30 min", icon: Brush },
  { id: "ritual", title: "Ritual Capilar", description: "Diagnóstico, hidratación profunda y masaje del cuero cabelludo.", longDescription: "Un cuidado pausado para recuperar equilibrio, elasticidad y brillo. Seleccionamos el tratamiento según las necesidades reales del cuero cabelludo y los largos.", benefits: ["Diagnóstico capilar", "Masaje relajante", "Hidratación y sellado de puntas"], imageSrc: image("photo-1521590832167-7bcbfaa6381f", 1400), imageAlt: "Ritual hidratante aplicado en el lavacabezas", priceFrom: 48, duration: "60 min", icon: Droplets },
  { id: "event", title: "Peinado de Evento", description: "Recogidos y ondas duraderas con una elegancia sin rigidez.", longDescription: "Creamos un peinado que acompaña tu look, tu tipo de evento y tu manera de moverte. Trabajamos fijación, volumen y textura para que dure sin perder ligereza.", benefits: ["Consulta previa de estilo", "Preparación y protección térmica", "Fijación flexible de larga duración"], imageSrc: image("photo-1562322140-8baeececf3df", 1400), imageAlt: "Peinado elegante preparado para un evento", priceFrom: 55, duration: "75 min", icon: Gem },
];

export const professionals: Professional[] = [
  {
    id: "laura",
    name: "Laura Baskuñana",
    role: "Directora creativa",
    experience: "+14 años de oficio",
    specialties: ["Corte personalizado", "Balayage", "Asesoría de imagen"],
    portraitSrc: image("photo-1494790108377-be9c29b29330", 900),
    portraitAlt: "Retrato de Laura Baskuñana, directora creativa del salón",
  },
  {
    id: "dani",
    name: "Dani Ros",
    role: "Barbero y estilista",
    experience: "+9 años de oficio",
    specialties: ["Barbería", "Corte corto", "Texturas"],
    portraitSrc: image("photo-1500648767791-00dcc994a43e", 900),
    portraitAlt: "Retrato de Dani Ros, barbero y estilista",
  },
  {
    id: "marta",
    name: "Marta Soler",
    role: "Especialista en color",
    experience: "+8 años de oficio",
    specialties: ["Colorimetría", "Rubios", "Corrección de color"],
    portraitSrc: image("photo-1534528741775-53994a69daeb", 900),
    portraitAlt: "Retrato de Marta Soler, especialista en color",
  },
  {
    id: "alvaro",
    name: "Álvaro León",
    role: "Estilista y terapeuta capilar",
    experience: "+7 años de oficio",
    specialties: ["Ritual capilar", "Peinado", "Cuero cabelludo"],
    portraitSrc: image("photo-1507003211169-0a1dd7228f2d", 900),
    portraitAlt: "Retrato de Álvaro León, estilista y terapeuta capilar",
  },
];

export const galleryItems: GalleryItem[] = [
  { id: "cut-1", src: image("photo-1595476108010-b4d1f102b1b1"), alt: "Corte bob pulido con flequillo suave", category: "Cortes", featured: true },
  { id: "cut-2", src: image("photo-1580618672591-eb180b1a973f"), alt: "Corte corto texturizado visto de perfil", category: "Cortes" },
  { id: "cut-3", src: image("photo-1522337360788-8b13dee7a37e"), alt: "Estilista trabajando un corte en el salón", category: "Cortes" },
  { id: "cut-4", src: image("photo-1562322140-8baeececf3df"), alt: "Melena natural recién cortada y peinada", category: "Cortes" },
  { id: "color-1", src: image("photo-1605497788044-5a32c7078486"), alt: "Color cobrizo luminoso con ondas", category: "Color", featured: true },
  { id: "color-2", src: image("photo-1595476108010-b4d1f102b1b1"), alt: "Balayage rubio de acabado natural", category: "Color" },
  { id: "color-3", src: image("photo-1580618672591-eb180b1a973f"), alt: "Coloración creativa con tonos cálidos", category: "Color" },
  { id: "color-4", src: image("photo-1616683693504-3ea7e9ad6fec"), alt: "Melena brillante con reflejos miel", category: "Color" },
  { id: "care-1", src: image("photo-1560869713-7d0a29430803"), alt: "Tratamiento capilar aplicado por una especialista", category: "Tratamientos", featured: true },
  { id: "care-2", src: image("photo-1521590832167-7bcbfaa6381f"), alt: "Ritual de cuidado en un salón profesional", category: "Tratamientos" },
  { id: "care-3", src: image("photo-1622286342621-4bd786c2447c"), alt: "Aplicación cuidadosa de producto y acabado", category: "Tratamientos" },
  { id: "care-4", src: image("photo-1595476108010-b4d1f102b1b1"), alt: "Cabello sano con brillo natural", category: "Tratamientos" },
];

export const testimonials: Testimonial[] = [
  { id: "lucia", quote: "Entendieron exactamente lo que quería. El balayage sigue precioso meses después.", author: "Lucía M.", service: "Balayage", rating: 5, avatarSrc: image("photo-1494790108377-be9c29b29330", 200) },
  { id: "carlos", quote: "Corte impecable, ambiente tranquilo y cero prisas. Por fin encontré mi sitio.", author: "Carlos R.", service: "Corte & Peinado", rating: 5, avatarSrc: image("photo-1500648767791-00dcc994a43e", 200) },
  { id: "marta", quote: "Mi pelo volvió a tener brillo y movimiento. El diagnóstico fue muy honesto.", author: "Marta P.", service: "Ritual Capilar", rating: 5, avatarSrc: image("photo-1534528741775-53994a69daeb", 200) },
  { id: "ines", quote: "Salí sintiéndome yo, solo que mejor. El corte crece con una forma increíble.", author: "Inés G.", service: "Corte & Peinado", rating: 5, avatarSrc: image("photo-1517841905240-472988babdf9", 200) },
  { id: "alvaro", quote: "La barba quedó natural y muy limpia. El ritual de toalla caliente es otro nivel.", author: "Álvaro S.", service: "Barba & Estilo", rating: 5, avatarSrc: image("photo-1507003211169-0a1dd7228f2d", 200) },
  { id: "sara", quote: "Me peinaron para una boda y aguantó perfecto hasta el final de la noche.", author: "Sara D.", service: "Peinado de Evento", rating: 5, avatarSrc: image("photo-1524504388940-b1c1722653e1", 200) },
  { id: "elena", quote: "Me explicaron cada paso y cuidaron muchísimo mi cabello. Volveré sin duda.", author: "Elena V.", service: "Keratina & Brillo", rating: 5, avatarSrc: image("photo-1544005313-94ddf0286df2", 200) },
];

export const transformations: Transformation[] = [
  {
    id: "balayage-luz",
    service: "Balayage",
    title: "Luz sin perder naturalidad",
    description: "Matices cálidos que aportan dimensión y movimiento respetando la base natural.",
    before: { src: image("photo-1560869713-7d0a29430803", 1800), alt: "Cabello antes de recuperar luminosidad y matices" },
    after: { src: image("photo-1560869713-7d0a29430803", 1800), alt: "Resultado de balayage cálido con ondas luminosas" },
  },
  {
    id: "corte-textura",
    service: "Corte & Peinado",
    title: "Una forma que se mueve contigo",
    description: "Capas precisas y textura ligera para que el corte funcione también fuera del salón.",
    before: { src: image("photo-1595476108010-b4d1f102b1b1", 1800), alt: "Cabello antes de definir la forma del corte" },
    after: { src: image("photo-1595476108010-b4d1f102b1b1", 1800), alt: "Corte terminado con textura y movimiento natural" },
  },
  {
    id: "ritual-brillo",
    service: "Ritual Capilar",
    title: "Brillo que se siente, no pesa",
    description: "Hidratación profunda y acabado pulido para recuperar suavidad sin restar volumen.",
    before: { src: image("photo-1616683693504-3ea7e9ad6fec", 1800), alt: "Cabello antes del ritual hidratante" },
    after: { src: image("photo-1616683693504-3ea7e9ad6fec", 1800), alt: "Cabello hidratado con reflejos miel y brillo natural" },
  },
  {
    id: "ondas-evento",
    service: "Peinado de Evento",
    title: "Ondas con memoria y ligereza",
    description: "Un acabado duradero, flexible y diseñado para verse bien desde todos los ángulos.",
    before: { src: image("photo-1562322140-8baeececf3df", 1800), alt: "Cabello antes de crear el peinado de evento" },
    after: { src: image("photo-1562322140-8baeececf3df", 1800), alt: "Peinado final con ondas suaves y definición" },
  },
];
