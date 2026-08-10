import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { Team } from "@/components/Team";
import { professionals } from "@/data/content";

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
});
