export type FeaturedImage = { id: number; url: string; width: number; height: number; alt: string };

export type CmsContent = {
  id: number;
  uuid: string;
  type: string;
  slug: string;
  title: string;
  excerpt: string;
  content: string;
  featured_image: FeaturedImage | null;
  meta: Record<string, unknown>;
  terms: Record<string, Array<{ id: number; slug: string; name: string }>>;
  published_at: string;
  modified_at: string;
};

export type SiteSettings = {
  business_name: string;
  tagline: string;
  phone: string;
  whatsapp: string;
  email: string;
  street_address: string;
  city: string;
  region: string;
  postal_code: string;
  country_code: string;
  opening_hours_summary: string;
  booking_path: string;
  status_path: string;
  instagram_url: string;
  youtube_url: string;
  facebook_url: string;
  default_pricing_disclaimer: string;
  demo_mode: boolean;
};

export type CmsDataset = {
  source: 'wordpress' | 'fixture';
  settings: SiteSettings;
  services: CmsContent[];
  problems: CmsContent[];
  faqs: CmsContent[];
  technicians: CmsContent[];
  locations: CmsContent[];
  repairCases: CmsContent[];
  warranties: CmsContent[];
  policies: CmsContent[];
};
