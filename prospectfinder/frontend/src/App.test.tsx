import { render, screen } from '@testing-library/react'
import { afterEach, describe, expect, it, vi } from 'vitest'
import App from './App'
import * as apiModule from './lib/api'

afterEach(() => vi.restoreAllMocks())

describe('App system status', () => {
  it('shows service states reported by the backend', async () => {
    vi.spyOn(apiModule, 'fetchHealth').mockResolvedValue({
      success: false,
      message: 'One or more services are unavailable.',
      data: { checks: { database: true, redis: false } },
    })

    render(<App />)

    expect(await screen.findByText('Database')).toBeInTheDocument()
    expect(screen.getAllByText('Connected')).toHaveLength(2)
    expect(screen.getByText('Unavailable')).toBeInTheDocument()
  })

  it('shows an error when the backend is unreachable', async () => {
    vi.spyOn(apiModule, 'fetchHealth').mockRejectedValue(new Error('network'))

    render(<App />)

    expect(await screen.findByText(/Backend unreachable/)).toBeInTheDocument()
  })
})
