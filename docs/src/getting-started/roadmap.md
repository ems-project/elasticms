# ElasticMS Release & Roadmap Policy

ElasticMS follows a **predictable, time-based release strategy**, inspired by mature ecosystems such
as Symfony.  
The objective is to balance **long-term stability**, **controlled evolution**, and **operational
safety**.

---

## Release Types

### Patch releases (`x.y.z`)

- Bug fixes only
- No functional changes
- No backward incompatibility
- Released as needed (typically weekly)
- Always safe to apply

### Minor releases (`x.y`)

- New features and technical improvements
- Backward compatible
- Short support window (≈ 2 months)
- Used to progressively introduce changes and deprecations

### Major releases (`x.0`)

- Stack upgrades and cleanup of deprecated features
- Possible breaking changes
- Introduced only when required by the roadmap
- Migration effort expected

---

## Long-Term Support (LTS)

LTS versions are the **recommended choice for production environments**.

- Extended support duration
- Bug fixes and security updates only
- No new features
- Reduced operational risk

Current and planned LTS versions:

- **ElasticMS 5.25 LTS** — supported until **June 2026**
- **ElasticMS 6.9 LTS** — supported until **December 2028**

---

## Minor Release Cycle (7.x)

- Regular minor releases
- Typical support duration: **~2 months**
- Used for:
    - functional evolution
    - technical improvements
    - progressive deprecations
- Minor versions act as **stepping stones toward the next LTS**

---

## Major Versions

Major versions mark **important technical transitions**:

- framework upgrades
- dependency cleanup
- removal of deprecated APIs
- alignment with upstream ecosystems (Symfony, PHP, Elasticsearch / OpenSearch)

Example:

- **ElasticMS 8.x** starts in **2028**, following the stabilization of the 7.x series and its LTS.

---

## Roadmap Overview

```mermaid
gantt
    title ElasticMS Roadmap (2025–2030)
    dateFormat  YYYY-MM
    axisFormat  %b %Y

    section 5.X
    5.25 since December 2024           :active, v525, 2025-09, 2026-06

    section 6.X
    6.7                                :done, v76, 2025-09, 61d
    6.8                                :done, v76, 2025-10, 61d
    6.9 LTS (Bugfix + Security)        :active, lts69, 2025-11, 2028-06

    section 7.x
    7.0                                :active, v70, 2026-02, 92d
    7.1                                :v71, 2026-04, 61d
    7.2                                :v72, 2026-05, 61d
    7.3                                :v73, 2026-06, 61d
    7.4                                :v74, 2026-07, 92d
    7.5                                :v75, 2026-09, 61d
    7.6                                :v76, 2026-10, 61d
    7.7                                :v77, 2026-11, 61d
    7.8                                :v78, 2026-12, 92d
    7.9                                :v79, 2027-02, 61d
    7.10                               :v710, 2027-03, 61d
    7.11                               :v711, 2027-04, 61d
    7.12                               :v712, 2027-05, 61d
    7.13                               :v713, 2027-06, 61d
    7.14                               :v714, 2027-07, 92d
    7.15                               :v715, 2027-09, 61d
    7.16 LTS (Bugfix + Security) until December 2029        :lts716, 2027-10, 2028-07

    section 8.x
    8.0                                :major, 2028-02, 92d
    8.1                                :v81, 2028-04, 61d
    8.2                                :v82, 2028-05, 61d
```

Remarks:

- The real start date of 5.25 is December 2024
- The real end date of 7.20 is December 2029

## Key Principles

- Prefer LTS versions for production systems
- Minor versions are short-lived by design
- Major upgrades are planned and documented
- The roadmap is aligned with upstream technologies
- Dates may evolve, but the release structure remains stable
