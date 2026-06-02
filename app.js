const seedJobs = [
  {
    id: 1,
    company: "Hikari Cloud",
    title: "Laravel / React フルスタックエンジニア",
    source: "Green",
    location: "東京",
    salaryMin: 650,
    salaryMax: 900,
    remote: "週3リモート",
    technologies: ["Laravel", "React", "AWS", "TypeScript"],
    receivedAt: "2026-06-01",
    status: "気になる",
    summary: "SaaS の管理画面刷新。バックエンド API とフロントエンド改善の両方を担当。",
  },
  {
    id: 2,
    company: "North Data Works",
    title: "Python データ基盤エンジニア",
    source: "エージェント",
    location: "東京",
    salaryMin: 700,
    salaryMax: 1000,
    remote: "フルリモート",
    technologies: ["Python", "AWS", "PostgreSQL", "dbt"],
    receivedAt: "2026-05-31",
    status: "未確認",
    summary: "求人メールから抽出した候補。データパイプラインと社内分析基盤の改善。",
  },
  {
    id: 3,
    company: "Minato Systems",
    title: "業務システム PHP エンジニア",
    source: "Wantedly",
    location: "大阪",
    salaryMin: 500,
    salaryMax: 700,
    remote: "出社中心",
    technologies: ["PHP", "Laravel", "MySQL"],
    receivedAt: "2026-05-30",
    status: "未確認",
    summary: "既存システム保守と追加開発。顧客先常駐のみの可能性あり。",
  },
  {
    id: 4,
    company: "Atlas HR Tech",
    title: "HR SaaS バックエンドエンジニア",
    source: "ビズリーチ",
    location: "東京",
    salaryMin: 750,
    salaryMax: 1100,
    remote: "ハイブリッド",
    technologies: ["Python", "FastAPI", "React", "AWS"],
    receivedAt: "2026-06-02",
    status: "応募したい",
    summary: "人材領域のプロダクト。メール解析、推薦ロジック、候補者体験の改善に近い内容。",
  },
];

const state = {
  jobs: [...seedJobs],
  lastSync: null,
};

const elements = {
  salary: document.querySelector("#salaryInput"),
  location: document.querySelector("#locationInput"),
  remote: document.querySelector("#remoteInput"),
  tech: document.querySelector("#techInput"),
  exclude: document.querySelector("#excludeInput"),
  search: document.querySelector("#searchInput"),
  list: document.querySelector("#jobList"),
  totalJobs: document.querySelector("#totalJobs"),
  hotJobs: document.querySelector("#hotJobs"),
  avgScore: document.querySelector("#avgScore"),
  applyJobs: document.querySelector("#applyJobs"),
  syncButton: document.querySelector("#syncButton"),
  syncNote: document.querySelector("#syncNote"),
  scoreButton: document.querySelector("#scoreButton"),
  resetButton: document.querySelector("#resetButton"),
};

function getCriteria() {
  return {
    salary: Number(elements.salary.value),
    location: elements.location.value,
    remote: elements.remote.checked,
    technologies: normalizeList(elements.tech.value),
    excludeWords: normalizeList(elements.exclude.value),
  };
}

function normalizeList(value) {
  return value
    .split(",")
    .map((item) => item.trim().toLowerCase())
    .filter(Boolean);
}

function scoreJob(job, criteria) {
  let score = 20;
  const reasons = [];
  const jobTech = job.technologies.map((tech) => tech.toLowerCase());
  const matchedTech = criteria.technologies.filter((tech) => jobTech.includes(tech));
  const text = `${job.company} ${job.title} ${job.summary} ${job.remote}`.toLowerCase();
  const blockedWords = criteria.excludeWords.filter((word) => text.includes(word));

  if (job.salaryMin >= criteria.salary) {
    score += 24;
    reasons.push(`年収下限 ${job.salaryMin}万円が希望を満たしています`);
  } else if (job.salaryMax >= criteria.salary) {
    score += 12;
    reasons.push("年収レンジ内に希望額が入っています");
  }

  if (criteria.location === "全国" || job.location === criteria.location) {
    score += 18;
    reasons.push(`${job.location}勤務が希望勤務地に合っています`);
  }

  if (criteria.remote && /リモート|ハイブリッド/.test(job.remote)) {
    score += 18;
    reasons.push(`${job.remote} の働き方です`);
  }

  score += Math.min(matchedTech.length * 8, 24);
  if (matchedTech.length > 0) {
    reasons.push(`技術一致: ${matchedTech.map(capitalize).join(", ")}`);
  }

  if (blockedWords.length > 0) {
    score -= 28;
    reasons.push(`除外候補: ${blockedWords.join(", ")}`);
  }

  return {
    score: Math.max(0, Math.min(100, score)),
    reasons: reasons.length ? reasons : ["条件との一致が少ないため、後で確認でよさそうです"],
  };
}

function capitalize(word) {
  const known = {
    aws: "AWS",
    php: "PHP",
    dbt: "dbt",
  };
  return known[word] || word.charAt(0).toUpperCase() + word.slice(1);
}

function render() {
  const criteria = getCriteria();
  const keyword = elements.search.value.trim().toLowerCase();
  const scoredJobs = state.jobs
    .map((job) => ({ ...job, match: scoreJob(job, criteria) }))
    .filter((job) => {
      if (!keyword) return true;
      return `${job.company} ${job.title} ${job.technologies.join(" ")}`.toLowerCase().includes(keyword);
    })
    .sort((a, b) => b.match.score - a.match.score);

  elements.list.innerHTML = scoredJobs.length
    ? scoredJobs.map(renderJobCard).join("")
    : '<p class="empty">条件に合う求人がありません。</p>';

  const allScores = state.jobs.map((job) => scoreJob(job, criteria).score);
  elements.totalJobs.textContent = state.jobs.length;
  elements.hotJobs.textContent = allScores.filter((score) => score >= 75).length;
  elements.avgScore.textContent = allScores.length
    ? Math.round(allScores.reduce((sum, score) => sum + score, 0) / allScores.length)
    : 0;
  elements.applyJobs.textContent = state.jobs.filter((job) => job.status === "応募したい").length;

  bindStatusChanges();
}

function renderJobCard(job) {
  const scoreClass = job.match.score < 45 ? "bad" : job.match.score < 75 ? "warning" : "";
  const statusOptions = ["未確認", "気になる", "応募したい", "応募済み", "見送り"]
    .map((status) => `<option value="${status}" ${status === job.status ? "selected" : ""}>${status}</option>`)
    .join("");

  return `
    <article class="job-card">
      <div class="job-main">
        <div>
          <h3 class="job-title">${job.title}</h3>
          <p class="company">${job.company} / ${job.source} / ${job.receivedAt}</p>
        </div>
        <div class="score ${scoreClass}" aria-label="マッチスコア">${job.match.score}</div>
      </div>
      <div class="job-meta">
        <span class="pill">${job.location}</span>
        <span class="pill">${job.salaryMin}-${job.salaryMax}万円</span>
        <span class="pill">${job.remote}</span>
        ${job.technologies.map((tech) => `<span class="pill">${tech}</span>`).join("")}
      </div>
      <p class="reason">${job.match.reasons.join("。")}。</p>
      <div class="job-footer">
        <select class="status-select" data-job-id="${job.id}" aria-label="${job.company} のステータス">
          ${statusOptions}
        </select>
      </div>
    </article>
  `;
}

function bindStatusChanges() {
  document.querySelectorAll(".status-select").forEach((select) => {
    select.addEventListener("change", (event) => {
      const target = event.currentTarget;
      const job = state.jobs.find((item) => item.id === Number(target.dataset.jobId));
      job.status = target.value;
      render();
    });
  });
}

function syncSampleMail() {
  const exists = state.jobs.some((job) => job.company === "Canvas AI");
  if (!exists) {
    state.jobs.unshift({
      id: Date.now(),
      company: "Canvas AI",
      title: "求人メール解析プロダクトのバックエンドエンジニア",
      source: "Gmail",
      location: "東京",
      salaryMin: 800,
      salaryMax: 1200,
      remote: "フルリモート",
      technologies: ["Python", "Laravel", "AWS", "PostgreSQL"],
      receivedAt: "2026-06-02",
      status: "未確認",
      summary: "Gmail 連携で求人情報を抽出し、推薦スコアを改善するポジション。",
    });
  }
  state.lastSync = new Date();
  elements.syncNote.textContent = `最終同期: ${state.lastSync.toLocaleString("ja-JP")}`;
  render();
}

function resetJobs() {
  state.jobs = [...seedJobs];
  state.lastSync = null;
  elements.syncNote.textContent = "最終同期: 未実行";
  render();
}

[
  elements.salary,
  elements.location,
  elements.remote,
  elements.tech,
  elements.exclude,
  elements.search,
].forEach((element) => {
  element.addEventListener("input", render);
  element.addEventListener("change", render);
});

elements.syncButton.addEventListener("click", syncSampleMail);
elements.scoreButton.addEventListener("click", render);
elements.resetButton.addEventListener("click", resetJobs);

render();
