# Package Unifier Runtime

## Purpose

Define the verified, experimental WordPress bootstrap boundaries retained during Universal Orchestrator V2 adoption. Adoption preserves application behavior and does not authorize global Composer dependency consolidation.

## Requirements

### Requirement: Experimental mode

The plugin SHALL remain an experimental package-dependency research project and SHALL NOT perform automatic cross-plugin dependency consolidation during WordPress requests or plugin activation.

#### Scenario: Ordinary WordPress request
- **WHEN** WordPress loads Package Unifier
- **THEN** Package Unifier does not scan other plugins, invoke Composer, or move vendor packages

### Requirement: Safe local autoload

The WordPress entrypoint SHALL load only the plugin-local `vendor/autoload.php` while runtime compatibility and package version isolation are unverified. A missing local autoloader SHALL return without public fatal errors; an administrator MAY see a localized notice.

#### Scenario: Local dependencies are missing
- **WHEN** the plugin-local Composer autoloader is absent
- **THEN** the bootstrap returns without requiring a global autoloader or producing a public fatal error

### Requirement: No activation-time global mutations

Activation and deactivation SHALL NOT create a global vendor directory, install packages, run a shell command, move dependency files, or alter unrelated plugin directories.

#### Scenario: Plugin activates with local dependencies
- **WHEN** the WordPress activation hook runs with the local autoloader available
- **THEN** activation does not mutate global Composer or other plugin state

### Requirement: WordPress hook isolation

The runtime hook registration SHALL remain limited to explicitly defined WordPress integration behavior, including translation loading, and SHALL NOT schedule package consolidation or dependency mutation.

#### Scenario: Hooks are initialized
- **WHEN** the plugin registers its WordPress hooks
- **THEN** no Composer command or vendor scan is registered

### Requirement: Guarded future consolidation

Any future cross-plugin consolidation SHALL require a separately approved OpenSpec change with compatible dependency constraints, load-order analysis, independent validation, atomic filesystem transactions, concurrency control, rollback and integration tests before runtime behavior may change.

#### Scenario: Historical backlog requests global vendor merging
- **WHEN** a historical requirement proposes global package unification
- **THEN** no runtime consolidation occurs without a new approved, verified specification and release

### Requirement: Historical records are inert

Historical PR bodies, issues, backlogs, decisions and branch cleanup intents in `historico/` SHALL remain recoverable evidence only and SHALL NOT independently authorize implementation, releases or deletion of GitHub references.

#### Scenario: An archived task remains open in its original backlog
- **WHEN** the archived backlog contains an unfinished task
- **THEN** the task remains non-executable until re-evaluated and separately approved under V2
