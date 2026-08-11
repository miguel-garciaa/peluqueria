import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { Team } from "@/components/Team";
import { professionals } from "@/data/content";
import type { BookingCatalogProfessional, BookingCatalogService } from "@/types";

const catalogServices: BookingCatalogService[] = [
  { id: "new-cut", name: "Corte técnico", description: null, durationMinutes: 45, priceFrom: 30, isCustom: false },
];
const catalogProfessionals: BookingCatalogProfessional[] = [
  { id: "new-professional", name: "Lucía Actualizada", role: "Especialista técnica", serviceIds: ["new-cut"] },
];

describe("Team", () => {
  it("shows every professional with their specialties", () => {
    render(<Team onBook={() => undefined} />);

    for (const professional of professionals) {
      expect(screen.getByRole("heading", { name: professional.name })).toBeInTheDocument();
      expect(screen.getByRole("list", { name: `Especialidades de ${professional.name}` })).toBeInTheDocument();
    }
  });

  it("starts a booking with the chosen professional", () => {
    const onBook = vi.fn();
    render(<Team onBook={onBook} />);
    fireEvent.click(screen.getByRole("button", { name: "Reservar con Marta" }));
    expect(onBook).toHaveBeenCalledWith("marta");
  });

  it("uses the current professionals and service assignments from the database catalog", () => {
    const onBook = vi.fn();
    render(<Team onBook={onBook} catalogProfessionals={catalogProfessionals} catalogServices={catalogServices} />);

    expect(screen.getByRole("heading", { name: "Lucía Actualizada" })).toBeInTheDocument();
    expect(screen.getByText("Especialista técnica")).toBeInTheDocument();
    expect(screen.getByText("Corte técnico")).toBeInTheDocument();
    expect(screen.queryByRole("heading", { name: "Marta Soler" })).not.toBeInTheDocument();

    fireEvent.click(screen.getByRole("button", { name: "Reservar con Lucía" }));
    expect(onBook).toHaveBeenCalledWith("new-professional");
  });
});
