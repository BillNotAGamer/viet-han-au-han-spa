# ADR-026: Phase 10 Public Navigation Integration

## Status

ACCEPTED

## Context

By Phase 10E, Services, Training, Blog, About, and Contact public routes exist. The shared header and footer must stop linking to temporary placeholders and must preserve the project localization model.

## Decision

- All six primary public navigation destinations use canonical routes.
- Vietnamese public navigation is prefixless.
- English public navigation uses `/en`.
- Primary navigation must not link to placeholder or future routes.
- Entity detail locale switching uses exact translated entity slugs.
- Static Page locale switching uses fixed route pairs.
- About and Contact static Page routing continues to use fixed machine keys, not translation slugs.
- Services has no dropdown affordance because no real submenu exists.
- Booking CTAs must not point to a nonexistent Booking route before the Booking phase.
- The shared header remains the centered-logo architecture with Homepage overlay mode and inner-page solid mode.

## Consequences

The public route surface is consistent across Phase 10 pages without adding new business features. Header polish is limited to documented metric adjustments and does not introduce a new layout architecture.
