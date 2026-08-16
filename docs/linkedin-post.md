# LinkedIn post — Custodia

## Main feed post (2,730 characters, fits LinkedIn's 3,000 limit)

---

64%.

As of February this year, 51,955 of the 80,812 people held in Nigerian custody were awaiting
trial, not serving a sentence. An independent panel spent March calling for inmate records to be
digitised, because most facilities still run on handwritten registers and paper case files.

When the paper is the only copy, people get lost in the system. Held past a release date because
the document proving the date went missing. Court appearances missed. Transfers with nobody
recorded as having approved them.

Nobody has to act in bad faith for that to happen. A paper system just has no way of noticing
when something has gone wrong.

So I built one that does.

Custodia is a custody and welfare management system: admissions, housing, sentences, court cases,
medical records, visits, movements and releases, with an audit trail on every action.

Live demo → https://custodia-rsvq.onrender.com
Sign in as admin@demo.com / password

The parts I'm proudest of aren't features, they're the decisions underneath them:

→ Every state change writes its audit entry inside the same database transaction. If the log and
the change could commit separately, the log would eventually start lying, which defeats the point
of keeping one.

→ Admission is a gated pipeline. You can't move past intake without recording the legal authority
for holding someone. Medical screening can only be signed off by medical staff.

→ Release runs a five-step review ending in a supervisor approval that officers cannot perform.
Some decisions should need a second person.

→ Medical records sit behind their own role. Officers see the operational alerts they need to
work safely and nothing clinical.

Then I audited my own code and found problems worth admitting to:

Cell capacity was only ever enforced by the UI filtering a dropdown. A direct API call could pack
unlimited people into a two-bed cell. The fix needed a row lock, not just a check, or two
simultaneous requests would both read the same free-bed count and overfill it anyway.

Worse, resolving an incident wrote "previously under review" into the audit log regardless of the
real previous status, and could skip review entirely. The audit trail was lying. In a system whose
entire premise is a trustworthy record, that was the most serious thing in the codebase.

I wrote regression tests for each one, then deliberately reverted the fixes to confirm the tests
actually failed. A test that passes with and without the fix is worth nothing.

The numbers: Laravel 13 + Vue 3 + TypeScript + PostgreSQL. 107 API endpoints, 28 models, 14
service classes, 20 policies, 36 migrations, ~14,500 lines. 184 passing tests.

Code: https://github.com/lesley-ayemi/custodia

I'm looking for backend or full-stack roles. If you're hiring, the demo is up and the code is
open.

#Laravel #VueJS #PHP #TypeScript #PostgreSQL #SoftwareEngineering #WebDevelopment #Nigeria

---

## Screenshot order (attach 4-5, in this order)

1. `docs/screenshots/dashboard.png` — leads with the strongest visual
2. `docs/screenshots/prisoner-profile.png` — shows depth and data modelling
3. `docs/screenshots/housing.png` — Block/Wing/Cell occupancy
4. `docs/screenshots/audit-log.png` — backs up the audit-trail claim
5. `docs/screenshots/prisoners.png` — optional, the searchable/sortable table

## Sources used

- NCoS figures (Feb 2026): 51,955 of 80,812 awaiting trial
  https://www.thecable.ng/ncos-51955-out-of-80812-inmates-in-nigerias-prisons-are-awaiting-trial/
- Independent panel calling for digitisation (Mar 2026)
  https://dailypost.ng/2026/03/26/independent-panel-calls-for-digitisation-staff-welfare-reforms-in-nigeria-correctional-service/
- Paper-based record keeping in Nigerian prisons (academic)
  https://www.researchgate.net/publication/375459249_DIGITALIZING_PRISON_MANAGEMENT_RECORDS_IN_A_DEVELOPING_ECONOMY
