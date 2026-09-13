import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { Testimonials } from "@/components/Testimonials";
import { StaggerTestimonials } from "@/components/ui/stagger-testimonials";

describe("StaggerTestimonials", () => {
  it("moves to the next testimonial with its control", () => {
    render(<StaggerTestimonials />);
    expect(screen.getByText(/Valoración activa: Entendieron exactamente/)).toBeInTheDocument();
    fireEvent.click(screen.getByRole("button", { name: "Valoración siguiente" }));
    expect(screen.getByText(/Valoración activa: Corte impecable/)).toBeInTheDocument();
  });

  it("supports keyboard, card selection and swipe navigation", () => {
    render(<StaggerTestimonials />);
    const carousel = screen.getByRole("region", { name: "Valoraciones de clientes" });

    fireEvent.keyDown(carousel, { key: "ArrowRight" });
    expect(screen.getByText(/Valoración activa: Corte impecable/)).toBeInTheDocument();
    fireEvent.keyDown(carousel, { key: "ArrowLeft" });
    expect(screen.getByText(/Valoración activa: Entendieron exactamente/)).toBeInTheDocument();
    fireEvent.click(screen.getByRole("button", { name: "Mostrar valoración de Marta P." }));
    expect(screen.getByText(/Valoración activa: Mi pelo volvió/)).toBeInTheDocument();
    fireEvent.pointerDown(carousel, { clientX: 200 });
    fireEvent.pointerUp(carousel, { clientX: 100 });
    expect(screen.getByText(/Valoración activa: Salí sintiéndome/)).toBeInTheDocument();
  });
});

describe("Testimonials metrics", () => {
  it("shows the final values without animation when reduced motion is enabled", () => {
    render(<Testimonials />);

    expect(screen.getByLabelText("4,7")).toHaveTextContent("4,7");
    expect(screen.getByLabelText("157")).toHaveTextContent("157");
    expect(screen.getByLabelText("6 días")).toHaveTextContent("6 días");
    expect(screen.getByLabelText("1 a 1")).toHaveTextContent("1 a 1");
  });
});
