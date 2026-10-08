# Proposal: WordPress bootstrap safety

## Why

The experimental plugin already has fail-closed runtime behavior, but its authoritative requirements are not represented in an operational OpenSpec project. Formalizing the verified behavior prevents accidental reactivation of unsafe global Composer mutations.

## What Changes

- Define normative, testable bootstrap and no-mutation requirements.
- Record the elimination of unauthenticated source-export artifacts.
- Install and validate the official OpenSpec CLI/tool integration in CI.
- Keep existing production-disabled dependency-unification behavior unchanged.

## Impact

No new runtime features; existing safeguards remain mandatory. Official CLI validation and spec synchronization are independent gates.
