import { ArrowDownRight } from "lucide-react";
import { professionals } from "@/data/content";
import { RevealTitle } from "@/components/ui/reveal-title";
import { ScrollReveal } from "@/components/ui/scroll-reveal";

interface TeamProps {
  onBook: (professionalId: string) => void;
}

export function Team({ onBook }: TeamProps) {
  return (
    <section id="equipo" className="team-section bg-porcelain pb-[clamp(5rem,10vw,9rem)]">
      <div className="container-shell border-t border-ink/12 pt-[clamp(5rem,9vw,8rem)]">
        <div className="mb-12 flex flex-col justify-between gap-6 lg:mb-16 lg:flex-row lg:items-end">
          <div>
            <p className="mb-3 font-semibold text-brass-deep">El equipo detrás de cada resultado</p>
            <RevealTitle
              label="Tu pelo, en buenas manos."
              className="display-title max-w-5xl"
              lines={[{ content: "Tu pelo," }, { content: "en buenas manos.", className: "text-brass-deep" }]}
            />
          </div>
          <p className="max-w-md text-pretty leading-7 text-taupe">
            Cuatro miradas distintas, una misma forma de trabajar: escuchar primero y recomendar solo lo que tu cabello necesita.
          </p>
        </div>

        <ScrollReveal className="team-lineup grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
          {professionals.map((professional, index) => (
            <article key={professional.id} className={index % 2 ? "lg:pt-14" : undefined}>
              <div className="group relative aspect-[4/5] overflow-hidden rounded-xl bg-mist">
                <img
                  src={professional.portraitSrc}
                  alt={professional.portraitAlt}
                  loading="lazy"
                  className="h-full w-full object-cover grayscale-[18%] transition-[filter,transform] duration-500 ease-[cubic-bezier(.16,1,.3,1)] group-hover:scale-[1.025] group-hover:grayscale-0"
                />
                <div className="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-ink/68 to-transparent" aria-hidden="true" />
                <span className="absolute bottom-4 left-4 rounded-full bg-white/92 px-3 py-1.5 text-xs font-bold text-ink backdrop-blur-sm">
                  {professional.experience}
                </span>
              </div>
              <div className="mt-5 flex items-start justify-between gap-4 border-b border-ink/12 pb-5">
                <div>
                  <h3 className="font-display text-2xl font-semibold tracking-[-0.025em]">{professional.name}</h3>
                  <p className="mt-1 text-sm font-semibold text-brass-deep">{professional.role}</p>
                </div>
                <span className="mt-1 font-display text-sm text-taupe" aria-hidden="true">0{index + 1}</span>
              </div>
              <ul className="mt-4 flex flex-wrap gap-2" aria-label={`Especialidades de ${professional.name}`}>
                {professional.specialties.map((specialty) => (
                  <li key={specialty} className="rounded-full bg-mist px-3 py-1.5 text-xs font-semibold text-ink/72">{specialty}</li>
                ))}
              </ul>
              <button
                type="button"
                onClick={() => onBook(professional.id)}
                className="group/button mt-5 flex min-h-11 items-center gap-2 text-sm font-bold text-ink outline-none transition-colors hover:text-brass-deep focus-visible:rounded-md focus-visible:ring-2 focus-visible:ring-brass-deep focus-visible:ring-offset-4 focus-visible:ring-offset-porcelain"
              >
                Reservar con {professional.name.split(" ")[0]}
                <ArrowDownRight className="size-4 transition-transform duration-200 group-hover/button:translate-x-0.5 group-hover/button:translate-y-0.5" aria-hidden="true" />
              </button>
            </article>
          ))}
        </ScrollReveal>
      </div>
    </section>
  );
}
