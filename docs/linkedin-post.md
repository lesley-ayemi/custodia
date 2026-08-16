# LinkedIn post for Custodia

Two versions. No em dashes in either. Copy whichever block you want.

## Short version

Roughly 950 characters. Reads in one glance, no "see more" needed on desktop.

---

64%.

As of February, 51,955 of the 80,812 people held in Nigerian custody had not been convicted of
anything. They were awaiting trial. Most facilities still track them on paper.

Files go missing. Someone then gets held past their release date because the document proving
the date is gone.

So I built Custodia, a prison management system that keeps a record of everything that happens
to someone in custody.

Live demo: https://custodia-rsvq.onrender.com
Sign in with admin@demo.com / password

Two decisions I care about in it:

Every change writes its audit entry inside the same database transaction as the change itself,
so the log cannot drift away from what actually happened.

Release runs a five step review that ends in a supervisor sign off no officer can perform.

Laravel 13, Vue 3, TypeScript, PostgreSQL. 107 API endpoints, 184 passing tests.

Code: https://github.com/lesley-ayemi/custodia

Open to backend and full stack roles.

#Laravel #VueJS #PHP #TypeScript #PostgreSQL #Nigeria

---

## Long version

Roughly 2,960 characters, just inside LinkedIn's 3,000 limit.

---

64%.

As of February this year, 51,955 of the 80,812 people held in Nigerian custody had not been
convicted of anything. They were awaiting trial. In March an independent panel called for inmate
records to be digitised, because most facilities still run on paper.

Those files go missing, and there is no second copy. Someone gets held past their release date
because the document proving the date is gone. A court appearance gets missed. A transfer happens
and nobody can say afterwards who approved it.

None of that needs anyone acting in bad faith. Paper has no way of noticing when something has
gone wrong.

So I built one that does.

Custodia handles admissions, housing, sentences, court cases, medical records, visits, movements
and releases, and keeps an audit trail of every action.

Live demo: https://custodia-rsvq.onrender.com
Sign in with admin@demo.com / password

The feature list is the boring part. Here is what I actually spent the time on.

Every state change writes its audit entry inside the same database transaction as the change
itself. If those could commit separately the log would drift away from reality, and an audit
trail you cannot trust is not worth keeping.

Admission runs as a gated pipeline. You cannot move past intake until someone records the legal
authority for holding that person, and only medical staff can sign off the medical screening.

Release goes through five steps and finishes with a supervisor approval that officers are not
able to perform. I wanted at least one point in the system where one person acting alone is not
enough.

Medical records sit behind their own role. Officers and supervisors see the operational alerts
they need to work safely, and nothing clinical.

Then I audited my own code, and found two things I had got wrong.

Cell capacity was only ever enforced by the interface filtering a dropdown. A direct API call
could put twenty people in a two bed cell. Fixing it needed a row lock as well as a
check, because two requests arriving together would otherwise both read the same free bed count
and overfill it anyway.

The second one was worse. Resolving an incident wrote "previously under review" into the audit
log no matter what the real previous status had been, and it let you skip the review step
entirely. So the record was describing something that had not happened. For a system built around
a trustworthy log, that was the worst bug in there.

I wrote regression tests for both, then reverted each fix on purpose to watch the tests fail.
Otherwise I would just be trusting a green tick.

Laravel 13, Vue 3, TypeScript, PostgreSQL. 107 API endpoints, 28 models, 14 service classes,
20 policies, 36 migrations, roughly 14,500 lines, 184 passing tests.

Code: https://github.com/lesley-ayemi/custodia

I am looking for backend or full stack work. The demo is up and the code is open, so have a dig
around.

#Laravel #VueJS #PHP #TypeScript #PostgreSQL #SoftwareEngineering #Nigeria

---

## Screenshots to attach, in this order

1. docs/screenshots/dashboard.png
2. docs/screenshots/prisoner-profile.png
3. docs/screenshots/housing.png
4. docs/screenshots/audit-log.png
5. docs/screenshots/prisoners.png (optional)

## Sources

NCoS figures, February 2026, 51,955 of 80,812 awaiting trial:
https://www.thecable.ng/ncos-51955-out-of-80812-inmates-in-nigerias-prisons-are-awaiting-trial/

Independent panel calling for digitisation, March 2026:
https://dailypost.ng/2026/03/26/independent-panel-calls-for-digitisation-staff-welfare-reforms-in-nigeria-correctional-service/

Paper based record keeping in Nigerian prisons:
https://www.researchgate.net/publication/375459249_DIGITALIZING_PRISON_MANAGEMENT_RECORDS_IN_A_DEVELOPING_ECONOMY
