export type UserRole = "superadmin" | "surveyor" | "user";

export type PoleCondition = "baik" | "rusak" | "perbaikan";

export type PoleType = "lurus" | "lengkung" | "tanpa_tiang" | "lainnya";

export type FoundationType = "beton" | "tanam" | "base_plate" | "lainnya";

export type Ownership = "dpu_kp" | "dishub" | "pln" | "lainnya";

export type User = {
  id: number;
  name: string;
  role: UserRole;
  email: string;
  phone?: string | null;
  photo_url?: string | null;
  address?: string | null;
  created_at?: string;
  updated_at?: string;
};

export type Pole = {
  id: number;
  customer_id: string;
  street_name: string;
  latitude: number;
  longitude: number;
  power_usage: number;
  condition: PoleCondition;
  pole_type: PoleType;
  height?: number | null;
  span_length?: number | null;
  foundation_type: FoundationType;
  ownership: Ownership;
  pln_transformer?: string | null;
  photo_url?: string | null;
  notes?: string | null;
  created_by_id?: number | null;
  updated_by_id?: number | null;
  created_by?: User | null;
  updated_by?: User | null;
  created_at?: string;
  updated_at?: string;
};

export type DailyReport = {
  id: number;
  user_id: number;
  pole_id: number;
  report_date: string;
  photo_url?: string;
  notes: string;
};

export type PoleFormInput = Omit<Pole, "id" | "photo_url" | "created_at" | "updated_at"> & {
  photo?: File | null;
};

export type ReportInput = {
  pole_id: number;
  notes: string;
  photo?: File | null;
};

export type UserFormInput = {
  name: string;
  email: string;
  phone?: string;
  address?: string;
  role: UserRole;
  password?: string;
};

export type ProfileFormInput = {
  name: string;
  email: string;
  phone?: string;
  address?: string;
  photo?: File | null;
};

export type PasswordFormInput = {
  current_password: string;
  password: string;
  password_confirmation: string;
};
