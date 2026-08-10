# Baskuñana Peluqueros Design System

## Direction

Contemporary salon at dusk: dark architectural surfaces, clean daylight imagery, and restrained brass details.

## Color

Colors are defined as OKLCH tokens in `src/index.css`: ink and charcoal for authority, pure porcelain for clarity, brass for conversion, and copper for warmth. Body text must maintain WCAG AA contrast.

## Typography

Bricolage Grotesque carries display moments with tight but readable spacing. Manrope handles navigation, body copy, forms, and utility text. Desktop hero titles may reach 12rem and primary section titles 10rem, using a brass asterisk as the recurring brand sign; mobile keeps the compact fluid scale.

## Shape & Depth

Cards use 12–16px radii, fine borders, and short low-diffusion shadows. Full-pill shapes are reserved for actions and compact filters.

## Navigation

The fixed navigation uses an ink surface after scroll, a fine brass progress line, numbered links, and a white conversion pill with a circular brass icon. The hero keeps the same navigation treatment transparent over photography.

## Motion

The hero uses one restrained load sequence: the image settles first, followed by masked title lines and supporting copy. Main section titles reveal line by line once on viewport entry. A thin reading-progress rule and subtle hero parallax may follow page scroll where scroll timelines are supported. The gallery uses a lazily loaded Three.js ribbon, testimonial cards fan out once on viewport entry, and the before/after control demonstrates itself with a single sweep. All motion pauses offscreen where applicable and has a `prefers-reduced-motion` fallback.
