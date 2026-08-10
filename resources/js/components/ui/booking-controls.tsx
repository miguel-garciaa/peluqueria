import { CalendarDays, Check, ChevronDown, ChevronLeft, ChevronRight, Clock3, Scissors } from "lucide-react";
import { useEffect, useId, useMemo, useRef, useState } from "react";
import { cn } from "@/lib/utils";

interface SelectOption {
  value: string;
  label: string;
  meta?: string;
}

interface SelectFieldProps {
  label: string;
  value: string;
  placeholder: string;
  options: SelectOption[];
  onChange: (value: string) => void;
  error?: string;
  disabled?: boolean;
  icon?: "service" | "time";
}

const triggerClass = "mt-2 flex min-h-14 w-full items-center gap-3 rounded-xl bg-white px-4 text-left text-sm text-ink shadow-[inset_0_0_0_1px_oklch(0.17_0.012_65/0.13)] outline-none transition-[box-shadow,background-color,transform] duration-200 hover:bg-mist/35 focus-visible:shadow-[inset_0_0_0_2px_var(--color-brass-deep),0_0_0_4px_oklch(0.83_0.082_78/0.22)] disabled:cursor-not-allowed disabled:opacity-50";

function useDismiss(open: boolean, onDismiss: () => void) {
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!open) return;
    const dismiss = (event: PointerEvent) => {
      if (ref.current && !ref.current.contains(event.target as Node)) onDismiss();
    };
    document.addEventListener("pointerdown", dismiss);
    return () => document.removeEventListener("pointerdown", dismiss);
  }, [onDismiss, open]);

  return ref;
}

export function SelectField({ label, value, placeholder, options, onChange, error, disabled, icon = "service" }: SelectFieldProps) {
  const [open, setOpen] = useState(false);
  const listId = useId();
  const fieldId = useId();
  const wrapperRef = useDismiss(open, () => setOpen(false));
  const selected = options.find((option) => option.value === value);
  return (
    <div ref={wrapperRef} className="relative">
      <span id={fieldId} className="text-sm font-semibold">{label}</span>
      <button
        type="button"
        role="combobox"
        aria-labelledby={fieldId}
        aria-controls={listId}
        aria-expanded={open}
        aria-haspopup="listbox"
        aria-invalid={!!error}
        value={value}
        disabled={disabled}
        className={cn(triggerClass, error && "shadow-[inset_0_0_0_1px_var(--color-danger)]")}
        onClick={() => setOpen((current) => !current)}
        onKeyDown={(event) => {
          if (event.key === "ArrowDown" || event.key === "ArrowUp") {
            event.preventDefault();
            setOpen(true);
          }
          if (event.key === "Escape") setOpen(false);
        }}
      >
        <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-mist text-brass-deep">
          {icon === "time" ? <Clock3 className="size-4" /> : selected ? <span className="font-display text-base font-bold">{selected.label.charAt(0)}</span> : <Scissors className="size-4" />}
        </span>
        <span className={cn("min-w-0 flex-1 truncate", !selected && "text-ink/65")}>{selected?.label ?? placeholder}</span>
        {selected?.meta && <span className="hidden shrink-0 text-xs font-medium text-taupe sm:block">{selected.meta}</span>}
        <ChevronDown className={cn("size-4 shrink-0 text-taupe transition-transform duration-200", open && "rotate-180")} />
      </button>

      {open && (
        <div id={listId} role="listbox" aria-labelledby={fieldId} className="absolute z-30 mt-2 w-full min-w-[17rem] overflow-hidden rounded-xl bg-white p-1.5 shadow-[0_16px_36px_-20px_oklch(0.17_0.012_65/0.65)] ring-1 ring-ink/10">
          {options.map((option) => {
            const isSelected = option.value === value;
            return (
              <button
                key={option.value}
                type="button"
                role="option"
                aria-selected={isSelected}
                className={cn("flex min-h-11 w-full items-center gap-3 rounded-lg px-3 text-left text-sm transition-colors hover:bg-mist focus-visible:bg-mist focus-visible:outline-none", isSelected && "bg-ink text-white hover:bg-ink")}
                onClick={() => {
                  onChange(option.value);
                  setOpen(false);
                }}
              >
                <span className="min-w-0 flex-1 truncate font-semibold">{option.label}</span>
                {option.meta && <span className={cn("shrink-0 text-xs", isSelected ? "text-white/70" : "text-taupe")}>{option.meta}</span>}
                {isSelected && <Check className="size-4 shrink-0 text-brass" />}
              </button>
            );
          })}
        </div>
      )}
      {error && <span className="mt-1.5 block text-xs font-medium text-danger">{error}</span>}
    </div>
  );
}

interface DateFieldProps {
  value: string;
  min: string;
  onChange: (value: string) => void;
  error?: string;
}

const toDate = (value: string) => new Date(`${value}T12:00:00`);
const toValue = (date: Date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
const monthFormatter = new Intl.DateTimeFormat("es-ES", { month: "long", year: "numeric" });
const dateFormatter = new Intl.DateTimeFormat("es-ES", { day: "numeric", month: "short", year: "numeric" });

export function DateField({ value, min, onChange, error }: DateFieldProps) {
  const [open, setOpen] = useState(false);
  const [visibleMonth, setVisibleMonth] = useState(() => {
    const initial = toDate(value || min);
    return new Date(initial.getFullYear(), initial.getMonth(), 1, 12);
  });
  const calendarId = useId();
  const labelId = useId();
  const wrapperRef = useDismiss(open, () => setOpen(false));

  useEffect(() => {
    if (!open) return;
    const selected = toDate(value || min);
    setVisibleMonth(new Date(selected.getFullYear(), selected.getMonth(), 1, 12));
  }, [open, min, value]);

  const days = useMemo(() => {
    const year = visibleMonth.getFullYear();
    const month = visibleMonth.getMonth();
    const firstWeekday = (new Date(year, month, 1, 12).getDay() + 6) % 7;
    const monthLength = new Date(year, month + 1, 0, 12).getDate();
    return [...Array.from({ length: firstWeekday }, () => null), ...Array.from({ length: monthLength }, (_, index) => new Date(year, month, index + 1, 12))];
  }, [visibleMonth]);

  const goToMonth = (offset: number) => setVisibleMonth((current) => new Date(current.getFullYear(), current.getMonth() + offset, 1, 12));

  return (
    <div ref={wrapperRef} className="relative">
      <span id={labelId} className="text-sm font-semibold">Fecha</span>
      <button
        type="button"
        role="combobox"
        aria-labelledby={labelId}
        aria-controls={calendarId}
        aria-expanded={open}
        aria-haspopup="dialog"
        aria-invalid={!!error}
        value={value}
        className={cn(triggerClass, error && "shadow-[inset_0_0_0_1px_var(--color-danger)]")}
        onClick={() => setOpen((current) => !current)}
        onKeyDown={(event) => event.key === "Escape" && setOpen(false)}
      >
        <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-mist text-brass-deep"><CalendarDays className="size-4" /></span>
        <span className={cn("min-w-0 flex-1 truncate", !value && "text-ink/65")}>{value ? dateFormatter.format(toDate(value)) : "Elige una fecha"}</span>
        <ChevronDown className={cn("size-4 shrink-0 text-taupe transition-transform duration-200", open && "rotate-180")} />
      </button>

      {open && (
        <div id={calendarId} role="dialog" aria-modal="false" aria-labelledby={labelId} className="absolute z-30 mt-2 w-[min(20rem,calc(100vw-4rem))] rounded-xl bg-white p-4 shadow-[0_16px_36px_-20px_oklch(0.17_0.012_65/0.65)] ring-1 ring-ink/10">
          <div className="mb-4 flex items-center justify-between">
            <strong className="font-display text-base capitalize">{monthFormatter.format(visibleMonth)}</strong>
            <div className="flex gap-1">
              <button type="button" aria-label="Mes anterior" className="grid size-10 place-items-center rounded-lg text-taupe transition-colors hover:bg-mist hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass-deep" onClick={() => goToMonth(-1)}><ChevronLeft className="size-4" /></button>
              <button type="button" aria-label="Mes siguiente" className="grid size-10 place-items-center rounded-lg text-taupe transition-colors hover:bg-mist hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass-deep" onClick={() => goToMonth(1)}><ChevronRight className="size-4" /></button>
            </div>
          </div>
          <div className="grid grid-cols-7 text-center text-[0.7rem] font-bold text-taupe" aria-hidden="true">
            {['L', 'M', 'X', 'J', 'V', 'S', 'D'].map((day) => <span key={day} className="py-1">{day}</span>)}
          </div>
          <div className="mt-1 grid grid-cols-7 gap-0.5">
            {days.map((date, index) => {
              if (!date) return <span key={`blank-${index}`} />;
              const dateValue = toValue(date);
              const isDisabled = dateValue < min || date.getDay() === 0;
              const isSelected = dateValue === value;
              const isToday = dateValue === min;
              return (
                <button
                  key={dateValue}
                  type="button"
                  disabled={isDisabled}
                  aria-label={dateFormatter.format(date)}
                  aria-pressed={isSelected}
                  className={cn("grid aspect-square min-h-9 place-items-center rounded-lg text-sm font-semibold outline-none transition-colors hover:bg-mist focus-visible:ring-2 focus-visible:ring-brass-deep disabled:cursor-not-allowed disabled:text-ink/22", isToday && !isSelected && "ring-1 ring-brass-deep/45", isSelected && "bg-ink text-white hover:bg-espresso")}
                  onClick={() => {
                    onChange(dateValue);
                    setOpen(false);
                  }}
                >
                  {date.getDate()}
                </button>
              );
            })}
          </div>
          <p className="mt-3 border-t border-ink/8 pt-3 text-xs text-taupe">Domingos cerrado · Selecciona un día disponible</p>
        </div>
      )}
      {error && <span className="mt-1.5 block text-xs font-medium text-danger">{error}</span>}
    </div>
  );
}
