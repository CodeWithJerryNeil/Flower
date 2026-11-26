import { useEffect, useState } from 'react'
import { fetchHealth, type HealthResponse } from './lib/api'

type State =
  | { kind: 'loading' }
  | { kind: 'ok'; health: HealthResponse }
  | { kind: 'error' }

function StatusRow({ label, up }: { label: string; up: boolean }) {
  return (
    <li className="flex items-center justify-between py-2">
      <span className="text-neutral-700">{label}</span>
      <span className={up ? 'font-medium text-green-800' : 'font-medium text-red-700'}>
        {up ? 'Connected' : 'Unavailable'}
      </span>
    </li>
  )
}

export default function App() {
  const [state, setState] = useState<State>({ kind: 'loading' })

  useEffect(() => {
    const controller = new AbortController()
    fetchHealth()
      .then((health) => setState({ kind: 'ok', health }))
      .catch(() => {
        if (!controller.signal.aborted) setState({ kind: 'error' })
      })
    return () => controller.abort()
  }, [])

  return (
    <main className="min-h-screen bg-neutral-50 px-4 py-16">
      <div className="mx-auto max-w-md rounded-xl border border-neutral-200 bg-white p-6">
        <h1 className="text-xl font-semibold text-neutral-900">ProspectFinder</h1>
        <p className="mt-1 text-sm text-neutral-500">System status</p>
        <div className="mt-4" aria-live="polite">
          {state.kind === 'loading' && <p className="text-neutral-500">Checking backend…</p>}
          {state.kind === 'error' && (
            <p className="text-red-700">Backend unreachable. Is the API running?</p>
          )}
          {state.kind === 'ok' && (
            <ul className="divide-y divide-neutral-100">
              <StatusRow label="API" up />
              <StatusRow label="Database" up={state.health.data.checks.database} />
              <StatusRow label="Redis" up={state.health.data.checks.redis} />
            </ul>
          )}
        </div>
      </div>
    </main>
  )
}
