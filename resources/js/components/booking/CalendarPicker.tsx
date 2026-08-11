import { ChevronLeft, ChevronRight } from "lucide-react";
import { useEffect, useState } from "react";
import { cn } from "@/lib/utils";

const isoDate = (date: Date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
const startOfToday = () => { const date = new Date(); date.setHours(0, 0, 0, 0); return date; };

interface CalendarPickerProps {
  value: string;
  onChange: (value: string) => void;
}

export function CalendarPicker({ value, onChange }: CalendarPickerProps) {
  const [month, setMonth] = useState(() => value ? new Date(`${value}T12:00:00`) : startOfToday());

  useEffect(() => {
    if (value) setMonth(new Date(`${value}T12:00:00`));
  }, [value]);

  const today = startOfToday();
  const maxDate = new Date(today);
  maxDate.setDate(maxDate.getDate() + 90);
  const firstDay = new Date(month.getFullYear(), month.getMonth(), 1);
  const monthLabel = new Intl.DateTimeFormat("es-ES", { month: "long", year: "numeric" }).format(firstDay);
  const leading = (firstDay.getDay() + 6) % 7;
  const days = new Date(month.getFullYear(), month.getMonth() + 1, 0).getDate();
  const cells = [...Array.from({ length: leading }, () => null), ...Array.from({ length: days }, (_, index) => new Date(month.getFullYear(), month.getMonth(), index + 1))];
  const previousDisabled = month.getFullYear() === today.getFullYear() && month.getMonth() === today.getMonth();
  const nextDisabled = month.getFullYear() === maxDate.getFullYear() && month.getMonth() === maxDate.getMonth();

  const moveMonth = (amount: number) => setMonth((current) => new Date(current.getFullYear(), current.getMonth() + amount, 1));

  return (
    <div className="rounded-2xl border border-ink/10 bg-white p-4 sm:p-5" aria-label="Calendario de citas">
      <div className="mb-4 flex items-center justify-between">
        <button type="button" disabled={previousDisabled} onClick={() => moveMonth(-1)} className="grid size-11 place-items-center rounded-full border border-ink/10 transition-colors hover:bg-mist disabled:opacity-25" aria-label="Mes anterior"><ChevronLeft className="size-4" /></button>
        <strong className="capitalize">{monthLabel}</strong>
        <button type="button" disabled={nextDisabled} onClick={() => moveMonth(1)} className="grid size-11 place-items-center rounded-full border border-ink/10 transition-colors hover:bg-mist disabled:opacity-25" aria-label="Mes siguiente"><ChevronRight className="size-4" /></button>
      </div>
      <div className="grid grid-cols-7 gap-1 text-center text-xs font-bold text-taupe" aria-hidden="true">
        {["L", "M", "X", "J", "V", "S", "D"].map((day) => <span key={day} className="py-2">{day}</span>)}
      </div>
      <div className="grid grid-cols-7 gap-1">
        {cells.map((date, index) => {
          if (!date) return <span key={`empty-${index}`} />;
          const dateValue = isoDate(date);
          const disabled = date < today || date > maxDate || date.getDay() === 0;
          const selected = value === dateValue;
          const label = new Intl.DateTimeFormat("es-ES", { weekday: "long", day: "numeric", month: "long" }).format(date);
          return <button key={dateValue} type="button" disabled={disabled} onClick={() => onChange(dateValue)} aria-label={label} aria-pressed={selected} className={cn("aspect-square min-h-10 rounded-xl text-sm font-semibold transition-[background-color,color,transform] hover:bg-mist focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brass-deep", selected && "bg-ink text-white hover:bg-ink", disabled && "text-ink/20 line-through hover:bg-transparent")}>{date.getDate()}</button>;
        })}
      </div>
      <p className="mt-3 text-xs text-taupe">Domingos cerrado · reservas hasta 90 días</p>
    </div>
  );
}
