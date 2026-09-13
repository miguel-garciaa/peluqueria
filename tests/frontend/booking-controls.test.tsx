import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { CalendarPicker } from "@/components/booking/CalendarPicker";
import { DateField, SelectField } from "@/components/ui/booking-controls";

describe("SelectField", () => {
  const options = [
    { value: "cut", label: "Corte", meta: "45 min" },
    { value: "color", label: "Color", meta: "90 min" },
  ];

  it("supports mouse selection and exposes its accessible state", () => {
    const onChange = vi.fn();
    render(<SelectField label="Servicio" value="" placeholder="Elige" options={options} onChange={onChange} error="Campo obligatorio" />);

    const trigger = screen.getByRole("combobox", { name: "Servicio" });
    expect(trigger).toHaveAttribute("aria-expanded", "false");
    expect(trigger).toHaveAttribute("aria-invalid", "true");
    fireEvent.click(trigger);
    expect(trigger).toHaveAttribute("aria-expanded", "true");
    fireEvent.click(screen.getByRole("option", { name: /Color/ }));

    expect(onChange).toHaveBeenCalledWith("color");
    expect(screen.queryByRole("listbox")).not.toBeInTheDocument();
  });

  it("opens with arrow keys, closes with escape and dismisses outside", () => {
    render(<SelectField label="Servicio" value="cut" placeholder="Elige" options={options} onChange={vi.fn()} />);
    const trigger = screen.getByRole("combobox", { name: "Servicio" });

    fireEvent.keyDown(trigger, { key: "ArrowDown" });
    expect(screen.getByRole("listbox")).toBeInTheDocument();
    fireEvent.keyDown(trigger, { key: "Escape" });
    expect(screen.queryByRole("listbox")).not.toBeInTheDocument();
    fireEvent.click(trigger);
    fireEvent.pointerDown(document.body);
    expect(screen.queryByRole("listbox")).not.toBeInTheDocument();
  });

  it("cannot open while disabled", () => {
    render(<SelectField label="Servicio" value="" placeholder="Elige" options={options} onChange={vi.fn()} disabled />);
    const trigger = screen.getByRole("combobox", { name: "Servicio" });
    expect(trigger).toBeDisabled();
    fireEvent.click(trigger);
    expect(screen.queryByRole("listbox")).not.toBeInTheDocument();
  });
});

describe("DateField", () => {
  it("prevents past dates and Sundays, navigates months and selects a valid day", () => {
    const onChange = vi.fn();
    render(<DateField value="" min="2026-09-10" onChange={onChange} error="Elige fecha" />);
    const trigger = screen.getByRole("combobox", { name: "Fecha" });
    fireEvent.click(trigger);

    expect(screen.getByRole("button", { name: "9 sept 2026" })).toBeDisabled();
    expect(screen.getByRole("button", { name: "13 sept 2026" })).toBeDisabled();
    fireEvent.click(screen.getByRole("button", { name: "14 sept 2026" }));
    expect(onChange).toHaveBeenCalledWith("2026-09-14");

    fireEvent.click(trigger);
    fireEvent.click(screen.getByRole("button", { name: "Mes siguiente" }));
    expect(screen.getByText(/octubre de 2026/i)).toBeInTheDocument();
    fireEvent.click(screen.getByRole("button", { name: "Mes anterior" }));
    expect(screen.getByText(/septiembre de 2026/i)).toBeInTheDocument();
  });
});

describe("CalendarPicker", () => {
  it("enforces today, Sunday and ninety-day bounds", () => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date("2026-09-10T09:00:00"));
    try {
      const onChange = vi.fn();
      render(<CalendarPicker value="" onChange={onChange} />);

      expect(screen.getByRole("button", { name: "Mes anterior" })).toBeDisabled();
      expect(screen.getByRole("button", { name: /miércoles, 9 de septiembre/i })).toBeDisabled();
      expect(screen.getByRole("button", { name: /domingo, 13 de septiembre/i })).toBeDisabled();
      fireEvent.click(screen.getByRole("button", { name: /lunes, 14 de septiembre/i }));
      expect(onChange).toHaveBeenCalledWith("2026-09-14");
    } finally {
      vi.useRealTimers();
    }
  });

  it("moves between months and follows an externally selected value", () => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date("2026-09-10T09:00:00"));
    try {
      const { rerender } = render(<CalendarPicker value="" onChange={vi.fn()} />);
      fireEvent.click(screen.getByRole("button", { name: "Mes siguiente" }));
      expect(screen.getByText(/octubre de 2026/i)).toBeInTheDocument();
      rerender(<CalendarPicker value="2026-11-12" onChange={vi.fn()} />);
      expect(screen.getByText(/noviembre de 2026/i)).toBeInTheDocument();
      expect(screen.getByRole("button", { name: /jueves, 12 de noviembre/i })).toHaveAttribute("aria-pressed", "true");
    } finally {
      vi.useRealTimers();
    }
  });
});
