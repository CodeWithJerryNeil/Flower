# Providers

No external providers are implemented yet. Each provider will be documented here when its
adapter is added, including:

- Official API and endpoints used
- Required environment variables (added to `backend/.env.example` at that time)
- Requested fields / field masks
- Rate limits and quotas
- Attribution requirements
- Storage and caching restrictions (what may be persisted, for how long)
- Terms-of-service review date and outcome

Candidate providers named in the playbook (not yet evaluated): Google Places API, Yelp Fusion,
Foursquare Places, a web search API, an AI provider, an email verification provider.
Terms and storage rules must be verified against current documentation before any of them is
enabled for persistent storage.
