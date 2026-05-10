import type { DailyReport, PasswordFormInput, Pole, PoleFormInput, ProfileFormInput, ReportInput, User, UserFormInput } from "@/lib/types";

const API_BASE_URL = process.env.NEXT_PUBLIC_PJU_API_URL ?? "http://127.0.0.1:8000/api";
const TOKEN_STORAGE_KEY = "pju_api_token";

export type PjuSnapshot = {
  user: User;
  poles: Pole[];
  reports: DailyReport[];
  users?: User[];
};

export type LoginCredentials = {
  email: string;
  password: string;
};

export type AuthSession = {
  token: string;
  user: User;
};

type ApiCollection<T> = {
  data: T[];
};

type ApiResource<T> = {
  data: T;
};

type ApiUser = {
  user: User;
};

type ApiLogin = ApiUser & {
  token: string;
};

export class ApiError extends Error {
  status: number;

  constructor(message: string, status: number) {
    super(message);
    this.name = "ApiError";
    this.status = status;
  }
}

export function getStoredToken() {
  if (typeof window === "undefined") {
    return null;
  }

  return window.localStorage.getItem(TOKEN_STORAGE_KEY);
}

export function storeToken(token: string) {
  window.localStorage.setItem(TOKEN_STORAGE_KEY, token);
}

export function clearStoredToken() {
  window.localStorage.removeItem(TOKEN_STORAGE_KEY);
}

export function getPoles(snapshot: PjuSnapshot, query?: string) {
  const normalizedQuery = query?.trim().toLowerCase();

  if (!normalizedQuery) {
    return snapshot.poles;
  }

  return snapshot.poles.filter((pole) =>
    [pole.street_name, pole.customer_id].some((value) => value.toLowerCase().includes(normalizedQuery)),
  );
}

export async function login(credentials: LoginCredentials) {
  const session = await request<ApiLogin>("/login", {
    method: "POST",
    body: JSON.stringify(credentials),
  });

  storeToken(session.token);

  return session;
}

export async function logout(token: string) {
  await request<{ message: string }>("/logout", {
    method: "POST",
    token,
  });
  clearStoredToken();
}

export async function getSnapshot(token: string): Promise<PjuSnapshot> {
  const me = await request<ApiUser>("/me", { token });
  const [poles, reports, users] = await Promise.all([
    request<ApiCollection<Pole>>("/poles", { token }),
    request<ApiCollection<DailyReport>>("/daily-reports", { token }),
    me.user.role === "superadmin" ? request<ApiCollection<User>>("/users", { token }) : Promise.resolve<ApiCollection<User> | null>(null),
  ]);

  return {
    user: me.user,
    poles: poles.data,
    reports: reports.data,
    users: users?.data,
  };
}

export async function createPole(token: string, input: PoleFormInput) {
  const response = await request<ApiResource<Pole>>("/poles", {
    method: "POST",
    token,
    body: createPoleFormData(input),
  });

  return response.data;
}

export async function updatePole(token: string, id: number, input: PoleFormInput) {
  const response = await request<ApiResource<Pole>>(`/poles/${id}`, {
    method: "POST",
    token,
    body: createPoleFormData(input, "PATCH"),
  });

  return response.data;
}

export async function deletePole(token: string, id: number) {
  await request<{ message: string }>(`/poles/${id}`, {
    method: "DELETE",
    token,
  });
}

export async function createUser(token: string, input: UserFormInput) {
  const response = await request<ApiResource<User>>("/users", {
    method: "POST",
    token,
    body: JSON.stringify(input),
  });

  return response.data;
}

export async function updateUser(token: string, id: number, input: UserFormInput) {
  const response = await request<ApiResource<User>>(`/users/${id}`, {
    method: "PATCH",
    token,
    body: JSON.stringify(input),
  });

  return response.data;
}

export async function deleteUser(token: string, id: number) {
  await request<{ message: string }>(`/users/${id}`, {
    method: "DELETE",
    token,
  });
}

export async function updateProfile(token: string, input: ProfileFormInput) {
  const formData = new FormData();
  formData.set("name", input.name);
  formData.set("email", input.email);
  formData.set("phone", input.phone ?? "");
  formData.set("address", input.address ?? "");

  if (input.photo) {
    formData.set("photo", input.photo);
  }

  return request<ApiUser>("/me/profile", {
    method: "POST",
    token,
    body: formData,
  });
}

export async function updatePassword(token: string, input: PasswordFormInput) {
  await request<{ message: string }>("/me/password", {
    method: "POST",
    token,
    body: JSON.stringify(input),
  });
}

function createPoleFormData(input: PoleFormInput, method?: "PATCH") {
  const formData = new FormData();

  if (method) {
    formData.set("_method", method);
  }

  formData.set("customer_id", input.customer_id);
  formData.set("street_name", input.street_name);
  formData.set("latitude", String(input.latitude));
  formData.set("longitude", String(input.longitude));
  formData.set("power_usage", String(input.power_usage));
  formData.set("condition", input.condition);
  formData.set("pole_type", input.pole_type);
  formData.set("foundation_type", input.foundation_type);
  formData.set("ownership", input.ownership);
  formData.set("pln_transformer", input.pln_transformer ?? "");
  formData.set("notes", input.notes ?? "");

  if (input.height != null) {
    formData.set("height", String(input.height));
  }

  if (input.span_length != null) {
    formData.set("span_length", String(input.span_length));
  }

  if (input.photo) {
    formData.set("photo", input.photo);
  }

  return formData;
}

export async function createDamageReport(token: string, input: ReportInput) {
  const formData = new FormData();
  formData.set("pole_id", String(input.pole_id));
  formData.set("notes", input.notes);

  if (input.photo) {
    formData.set("photo", input.photo);
  }

  const response = await request<ApiResource<DailyReport>>("/daily-reports", {
    method: "POST",
    token,
    body: formData,
  });

  return response.data;
}

async function request<T>(
  path: string,
  options: RequestInit & {
    token?: string;
  } = {},
): Promise<T> {
  const headers = new Headers(options.headers);

  if (!(options.body instanceof FormData)) {
    headers.set("Content-Type", "application/json");
  }

  if (options.token) {
    headers.set("Authorization", `Bearer ${options.token}`);
  }

  headers.set("Accept", "application/json");

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers,
  });

  if (!response.ok) {
    const message = await readErrorMessage(response);
    throw new ApiError(message, response.status);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return response.json() as Promise<T>;
}

async function readErrorMessage(response: Response) {
  try {
    const body = (await response.json()) as {
      message?: string;
      errors?: Record<string, string[]>;
    };

    const firstValidationError = body.errors ? Object.values(body.errors).flat()[0] : null;

    return firstValidationError ?? body.message ?? "Request API gagal.";
  } catch {
    return "Request API gagal.";
  }
}
