# Phase 10E Locale Switch Matrix

| Surface | VI target | EN target | Status |
| --- | --- | --- | --- |
| Home | `/` | `/en` | PASS |
| Services index | `/dich-vu` | `/en/services` | PASS |
| Service detail | exact VI Service slug | exact EN Service slug | PASS from Phase 10A tests |
| Training index | `/dao-tao` | `/en/training` | PASS |
| Training detail | exact VI TrainingCourse slug | exact EN TrainingCourse slug | PASS from Phase 10B tests |
| Blog index | `/blog` | `/en/blog` | PASS |
| Blog detail | exact VI Post slug | exact EN Post slug | PASS from Phase 10C tests |
| About | `/gioi-thieu` | `/en/about` | PASS |
| Contact | `/lien-he` | `/en/contact` | PASS |

Missing target translation fallback:
- Services detail -> target Services index
- Training detail -> target Training index
- Blog detail -> target Blog index
- About/Contact -> target Homepage

Slug policy:
- Entity detail switching is entity-based and uses translated slugs.
- Static Page switching uses fixed route pairs.
