import type { LucideIcon } from "lucide-react";

export interface Service {
  id: string;
  title: string;
  description: string;
  longDescription: string;
  benefits: string[];
  imageSrc: string;
  imageAlt: string;
  priceFrom: number | null;
  duration: string;
  icon: LucideIcon;
}

export interface Professional {
  id: string;
  name: string;
  role: string;
  experience: string;
  specialties: string[];
  portraitSrc: string;
  portraitAlt: string;
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
  professionalId: string;
  customDetails: string;
  date: string;
  timeSlot: string;
}

export type SubmissionStatus = "idle" | "submitting" | "success" | "error";

export interface CurrentUser {
  name: string;
  email: string;
  phone: string | null;
  avatarUrl: string | null;
  isAdmin?: boolean;
}

export interface BookingCatalogService {
  id: string;
  name: string;
  description: string | null;
  imageUrl?: string | null;
  durationMinutes: number;
  priceFrom: number | null;
  isCustom: boolean;
}

export interface BookingCatalogProfessional {
  id: string;
  name: string;
  role: string | null;
  imageUrl?: string | null;
  serviceIds: string[];
}

export interface BookingCatalog {
  services: BookingCatalogService[];
  professionals: BookingCatalogProfessional[];
}

export interface AvailabilitySlot {
  time: string;
  period: "morning" | "afternoon";
  professional: { slug: string; name: string };
}

export interface UserAppointment {
  reference: string;
  service: string;
  professional: string;
  customDetails: string | null;
  startsAt: string;
  endsAt: string;
  status: "pending" | "confirmed" | "cancelled" | "completed";
  canCancel: boolean;
  cancelUrl: string;
}
