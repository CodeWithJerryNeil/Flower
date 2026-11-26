import axios from 'axios'

/**
 * Minimal API client for Phase 0 connectivity checks.
 * Authentication handling, typed envelopes and error normalisation arrive in Phase 9.
 */
export const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: { Accept: 'application/json' },
  timeout: 10_000,
})

export interface HealthResponse {
  success: boolean
  message: string
  data: { checks: { database: boolean; redis: boolean } }
}

export async function fetchHealth(): Promise<HealthResponse> {
  const response = await api.get<HealthResponse>('/health', {
    // A 503 still carries a valid health body; surface it rather than throwing.
    validateStatus: (status) => status === 200 || status === 503,
  })
  return response.data
}
