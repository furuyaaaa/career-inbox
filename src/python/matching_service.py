import json
import sys


SEMANTIC_GROUPS = {
    "occupation": [
        [
            "営業",
            "法人営業",
            "フィールドセールス",
            "インサイドセールス",
            "アカウントエグゼクティブ",
            "カスタマーサクセス",
            "カスタマーサポート",
            "CS",
            "導入支援",
            "オンボーディング",
        ],
        ["マーケティング", "マーケター", "広告運用", "グロース", "CRMマーケティング"],
        ["エンジニア", "バックエンド", "フロントエンド", "Laravel", "Python", "Webエンジニア"],
        ["人事", "採用", "HR", "労務"],
        ["経理", "財務", "会計"],
    ],
    "industry": [
        ["IT", "SaaS", "クラウド", "ソフトウェア", "AI", "DX"],
        ["人材", "HR", "採用", "転職"],
        ["金融", "FinTech", "決済", "保険"],
        ["医療", "ヘルスケア", "介護"],
    ],
    "skill": [
        ["CRM", "Salesforce", "HubSpot", "SFA"],
        ["データ分析", "SQL", "BI", "Tableau", "Looker"],
        ["法人営業", "BtoB営業", "アカウント営業", "提案営業"],
        ["採用", "ダイレクトリクルーティング", "面接", "スカウト"],
        ["Laravel", "PHP", "バックエンド"],
        ["Python", "機械学習", "ML", "データサイエンス"],
    ],
    "remote": [
        ["フルリモート", "リモート", "在宅勤務", "全国"],
        ["ハイブリッド", "週3リモート", "一部リモート", "リモート可"],
        ["出社中心", "出社", "オフィス勤務"],
    ],
}


def normalize(value):
    return str(value or "").strip().lower()


def contains_any(value, candidates):
    if not value:
        return False

    lowered = normalize(value)
    return any(candidate and normalize(candidate) in lowered for candidate in candidates)


def semantic_matches(value, candidates, category):
    matched = []
    lowered_value = normalize(value)

    if not lowered_value:
        return matched

    for candidate in candidates:
        lowered_candidate = normalize(candidate)
        if not lowered_candidate:
            continue

        if lowered_candidate in lowered_value:
            matched.append(candidate)
            continue

        for group in SEMANTIC_GROUPS.get(category, []):
            normalized_group = [normalize(item) for item in group]
            if lowered_candidate in normalized_group and any(item in lowered_value for item in normalized_group):
                matched.append(candidate)
                break

    return list(dict.fromkeys(matched))


def matched_skills(job, profile):
    job_skills = {str(skill).lower() for skill in job.get("technologies", [])}
    title = str(job.get("title") or "").lower()
    memo = str(job.get("memo") or "").lower()
    searchable = " ".join([title, memo, " ".join(job_skills)])

    matches = []
    for skill in profile.get("preferred_technologies", []):
        normalized = normalize(skill)
        if normalized and (normalized in job_skills or normalized in title or normalized in memo):
            matches.append(skill)
            continue

        if semantic_matches(searchable, [skill], "skill"):
            matches.append(skill)

    return list(dict.fromkeys(matches))


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
    else:
        occupations = semantic_matches(job.get("occupation"), profile.get("preferred_occupations", []), "occupation")
        if occupations:
            total += 10
            reasons.append("近い職種として一致: " + ", ".join(occupations))

    if contains_any(job.get("industry"), profile.get("preferred_industries", [])):
        total += 10
        reasons.append("業界が希望に合っています")
    else:
        industries = semantic_matches(job.get("industry"), profile.get("preferred_industries", []), "industry")
        if industries:
            total += 6
            reasons.append("近い業界として一致: " + ", ".join(industries))

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
    elif remote_types and remote_type:
        matched_remote_types = semantic_matches(remote_type, remote_types, "remote")
        if matched_remote_types:
            total += 10
            reasons.append("近いリモート条件として一致: " + ", ".join(matched_remote_types))
        elif profile.get("remote_required"):
            total -= 12
            reasons.append("リモート条件は要確認です")
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
