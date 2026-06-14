import json
import sys


def contains_any(value, candidates):
    if not value:
        return False

    lowered = str(value).lower()
    return any(candidate and str(candidate).lower() in lowered for candidate in candidates)


def matched_skills(job, profile):
    job_skills = {str(skill).lower() for skill in job.get("technologies", [])}
    title = str(job.get("title") or "").lower()
    memo = str(job.get("memo") or "").lower()

    matches = []
    for skill in profile.get("preferred_technologies", []):
        normalized = str(skill).lower()
        if normalized and (normalized in job_skills or normalized in title or normalized in memo):
            matches.append(skill)

    return matches


def matched_excluded_keywords(job, profile):
    text = " ".join(
        str(value or "")
        for value in [
            job.get("company_name"),
            job.get("title"),
            job.get("occupation"),
            job.get("industry"),
            job.get("source"),
            job.get("agent_name"),
            job.get("location"),
            job.get("employment_type"),
            job.get("remote_type"),
            " ".join(str(skill) for skill in job.get("technologies", [])),
            job.get("memo"),
        ]
    ).lower()

    return [
        keyword
        for keyword in profile.get("excluded_keywords", [])
        if keyword and str(keyword).lower() in text
    ]


def score(payload):
    job = payload.get("job", {})
    profile = payload.get("profile", {})
    total = 20
    reasons = []

    excluded = matched_excluded_keywords(job, profile)

    if contains_any(job.get("occupation"), profile.get("preferred_occupations", [])):
        total += 16
        reasons.append("職種カテゴリが希望に合っています")

    if contains_any(job.get("industry"), profile.get("preferred_industries", [])):
        total += 10
        reasons.append("業界が希望に合っています")

    desired_salary = profile.get("desired_salary_min")
    salary_min = job.get("salary_min")
    salary_max = job.get("salary_max")
    if desired_salary and salary_min:
        if salary_min >= desired_salary:
            total += 24
            reasons.append(f"年収下限 {salary_min}万円が希望を満たしています")
        elif salary_max and salary_max >= desired_salary:
            total += 12
            reasons.append("年収レンジ内に希望額が入っています")

    locations = profile.get("preferred_locations", [])
    location = job.get("location")
    if locations and location and (location in locations or "全国" in locations or location == "全国"):
        total += 18
        reasons.append("勤務地が希望条件に合っています")

    remote_types = profile.get("preferred_remote_types", [])
    remote_type = job.get("remote_type")
    if remote_types and remote_type and remote_type in remote_types:
        total += 18
        reasons.append("リモート条件が合っています")
    elif profile.get("remote_required"):
        total -= 12
        reasons.append("リモート条件は要確認です")

    skills = matched_skills(job, profile)
    if skills:
        total += min(len(skills) * 8, 24)
        reasons.append("スキル・経験一致: " + ", ".join(skills))

    if excluded:
        total -= 28
        reasons.append("除外候補: " + ", ".join(excluded))

    total = max(0, min(100, total))

    return {
        "score": total,
        "reasons": reasons or ["条件との一致が少ないため、後で確認でよさそうです"],
    }


def main():
    payload = json.load(sys.stdin)
    json.dump(score(payload), sys.stdout, ensure_ascii=False)


if __name__ == "__main__":
    main()
