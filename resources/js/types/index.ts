import type { LucideIcon } from "lucide-react";

export interface Service {
  id: string;
  title: string;
  description: string;
  longDescription: string;
  benefits: string[];
  imageSrc: string;
  imageAlt: string;
  priceFrom: number;
  duration: string;
  icon: LucideIcon;
}

export type GalleryCategory = "Todos" | "Cortes" | "Color" | "Tratamientos";
export type GalleryItemCategory = Exclude<GalleryCategory, "Todos">;

export interface GalleryItem {
  id: string;
  src: string;
  alt: string;
  category: GalleryItemCategory;
  featured?: boolean;
}

export interface Testimonial {
  id: string;
  quote: string;
  author: string;
  service: string;
  rating: number;
  avatarSrc: string;
}

export interface Transformation {
  id: string;
  service: string;
  title: string;
  description: string;
  before: { src: string; alt: string };
  after: { src: string; alt: string };
}

export interface BookingFormData {
  fullName: string;
  phone: string;
  serviceId: string;
  date: string;
  timeSlot: string;
}

export type SubmissionStatus = "idle" | "submitting" | "success" | "error";
