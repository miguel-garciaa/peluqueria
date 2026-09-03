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
