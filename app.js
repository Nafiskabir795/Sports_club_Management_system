// Declares a JavaScript variable.
const API = "backend/";

// Defines a reusable JavaScript function.
async function api(file, options = {}) {
  // Declares a JavaScript variable.
  const response = await fetch(API + file, {
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json",
      ...(window.SPORTS_CSRF && options.method && options.method.toUpperCase() !== "GET" ? {"X-CSRF-Token": window.SPORTS_CSRF} : {}),
      ...(options.headers || {})
    },
    ...options
  });

  // Declares a JavaScript variable.
  let data;
  // Starts error-handling logic.
  try {
    // Executes the current JavaScript statement/block.
    data = await response.json();
  // Executes the current JavaScript statement/block.
  } catch {
    // Executes the current JavaScript statement/block.
    data = { success: false, message: "Invalid server response." };
  // Executes the current JavaScript statement/block.
  }

  // Checks a condition before continuing.
  if (!response.ok) {
    // Executes the current JavaScript statement/block.
    throw new Error(data.message || `Request failed (${response.status})`);
  // Executes the current JavaScript statement/block.
  }

  // Returns a value from the current function.
  return data;
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
function showMessage(id, message, type = "success") {
  // Declares a JavaScript variable.
  const el = document.getElementById(id);
  // Checks a condition before continuing.
  if (!el) return;
  // Changes the visible text of an HTML element.
  el.textContent = message;
  // Changes CSS classes on an HTML element.
  el.className = `message ${type}`;
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
function hideMessage(id) {
  // Declares a JavaScript variable.
  const el = document.getElementById(id);
  // Checks a condition before continuing.
  if (el) el.className = "message hidden";
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
function esc(value) {
  // Returns a value from the current function.
  return String(value ?? "").replace(/[&<>"']/g, ch => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"
  // Executes the current JavaScript statement/block.
  }[ch]));
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function logout() {
  // Sends a request to the backend.
  try { await api("logout.php", { method: "POST", body: "{}" }); }
  // Navigates the browser to another page.
  finally { window.location.href = "index.html"; }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function requireRole(role) {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api(`session_check.php?role=${encodeURIComponent(role)}`);
    // Returns a value from the current function.
    if (data.csrf_token) window.SPORTS_CSRF = data.csrf_token;
    return data.user;
  // Executes the current JavaScript statement/block.
  } catch {
    // Navigates the browser to another page.
    window.location.href = role === "coach" ? "admin_login.html" : "student_login.html";
    // Returns a value from the current function.
    return null;
  // Executes the current JavaScript statement/block.
  }
// Executes the current JavaScript statement/block.
}

/* PASSWORD VISIBILITY */
// Adds a small show/hide control to password fields without changing submitted values.
function initPasswordToggles() {
  document.querySelectorAll('input[type="password"]').forEach(input => {
    if (input.dataset.toggleReady === "true") return;

    const wrapper = document.createElement("div");
    wrapper.className = "password-field";
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const button = document.createElement("button");
    button.type = "button";
    button.className = "password-toggle";
    button.textContent = "SHOW";
    button.setAttribute("aria-label", "Show password");

    button.addEventListener("click", () => {
      const visible = input.type === "text";
      input.type = visible ? "password" : "text";
      button.textContent = visible ? "SHOW" : "HIDE";
      button.setAttribute("aria-label", visible ? "Show password" : "Hide password");
    });

    wrapper.appendChild(button);
    input.dataset.toggleReady = "true";
  });
}

/* LOGIN */
const loginForm = document.getElementById("loginForm");
// Checks a condition before continuing.
if (loginForm) {
  // Attaches an event handler.
  loginForm.addEventListener("submit", async event => {
    // Executes the current JavaScript statement/block.
    event.preventDefault();
    // Executes the current JavaScript statement/block.
    hideMessage("loginMessage");

    // Declares a JavaScript variable.
    const identifier = document.getElementById("identifier").value.trim();
    // Declares a JavaScript variable.
    const password = document.getElementById("password").value;

    // Starts error-handling logic.
    try {
      // Declares a JavaScript variable.
      const data = await api("login.php", {
        method: "POST",
        // Converts JavaScript data to JSON text.
        body: JSON.stringify({ identifier, password })
      // Executes the current JavaScript statement/block.
      });
      // Navigates the browser to another page.
      window.location.href = data.redirect;
    // Executes the current JavaScript statement/block.
    } catch (error) {
      // Executes the current JavaScript statement/block.
      showMessage("loginMessage", error.message, "error");
    // Executes the current JavaScript statement/block.
    }
  // Executes the current JavaScript statement/block.
  });
// Executes the current JavaScript statement/block.
}

/* PORTAL */
async function initPortal() {
  // Declares a JavaScript variable.
  const user = await requireRole("student");
  // Checks a condition before continuing.
  if (!user) return;

  // Finds an HTML element in the DOM.
  document.getElementById("welcomeName").textContent = user.name;

  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("student_sports.php");
    // Declares a JavaScript variable.
    const box = document.getElementById("portalSports");

    // Declares a JavaScript variable.
    const registered = new Map(data.sports.map(s => [Number(s.sport_id), s]));
    // Declares a JavaScript variable.
    const sports = [
      { id: 1, name: "Football", icon: "⚽" },
      { id: 2, name: "Volleyball", icon: "🏐" },
      { id: 3, name: "Cricket", icon: "🏏" }
    // Executes the current JavaScript statement/block.
    ];

    // Executes the current JavaScript statement/block.
    box.innerHTML = sports.map(sport => {
      // Declares a JavaScript variable.
      const choice = registered.get(sport.id);
      // Declares a JavaScript variable.
      const locked = !choice;
      // Returns a value from the current function.
      return `
        <article class="sport-card ${locked ? "locked" : "unlocked"}" data-sport="${sport.name.toLowerCase()}">
          <div class="sport-visual" aria-hidden="true">${sport.icon}</div>
          ${locked ? `<span class="sport-lock">🔒</span>` : `<span class="registered-chip">REGISTERED SPORT</span>`}
          <div class="sport-copy">
            <h2>${esc(sport.name)}</h2>
            <p>${locked ? "This sport is not registered for your account." : `Position: ${esc(choice.position_name || "Not assigned")}`}</p>
          </div>
          ${locked
            ? `<span class="sport-status">RESTRICTED</span>`
            : `<a class="sport-link" href="dashboard.html?sport=${sport.id}">OPEN DASHBOARD <span>→</span></a>`}
        </article>`;
    // Executes the current JavaScript statement/block.
    }).join("");
  // Executes the current JavaScript statement/block.
  } catch (error) {
    // Executes the current JavaScript statement/block.
    showMessage("portalMessage", error.message, "error");
  // Executes the current JavaScript statement/block.
  }
// Executes the current JavaScript statement/block.
}

/* STUDENT DASHBOARD */
let currentStudentUser = null;

// Stores data returned by the dashboard APIs so the summary cards can update without extra requests.
const studentDashboardState = {
  schedule: [],
  selections: [],
  performance: []
};

// Converts a list of performance records into an average overall score.
function averageOverallScore(rows) {
  const scores = rows.filter(row => row.overall_score !== null && row.overall_score !== "" && row.overall_score !== undefined).map(row => Number(row.overall_score)).filter(Number.isFinite);
  if (!scores.length) return null;
  return Math.round((scores.reduce((sum, score) => sum + score, 0) / scores.length) * 10) / 10;
}

// Updates the four summary cards using data already loaded for the dashboard.
function updateStudentSummary() {
  const now = new Date();
  const next = studentDashboardState.schedule
    .filter(item => {
      const sessionTime = new Date(`${item.session_date}T${item.start_time || "00:00"}`);
      return String(item.status).toLowerCase() !== "cancelled" && sessionTime >= now;
    })
    .sort((a, b) => `${a.session_date} ${a.start_time}`.localeCompare(`${b.session_date} ${b.start_time}`))[0];
  const attendanceRows = studentDashboardState.schedule.filter(item => ["Present", "Absent"].includes(item.attendance));
  const presentCount = attendanceRows.filter(item => item.attendance === "Present").length;
  const attendanceRate = attendanceRows.length ? Math.round((presentCount / attendanceRows.length) * 100) : null;
  const selected = studentDashboardState.selections.filter(item => item.selection_status === "Selected").length;
  const performanceScore = averageOverallScore(studentDashboardState.performance);

  document.getElementById("summaryNextSession").textContent = next ? next.title : "No upcoming session";
  document.getElementById("summaryNextSessionMeta").textContent = next ? `${next.session_date} • ${next.start_time} • ${next.location || "Location TBA"}` : "Check back for your next session";
  document.getElementById("summaryAttendance").textContent = attendanceRate === null ? "—" : `${attendanceRate}%`;
  document.getElementById("summaryAttendanceMeta").textContent = attendanceRows.length ? `${presentCount} present of ${attendanceRows.length} recorded` : "No attendance recorded yet";
  document.getElementById("summarySelection").textContent = selected ? "SELECTED" : studentDashboardState.selections.length ? "See status" : "—";
  document.getElementById("summarySelectionMeta").textContent = studentDashboardState.selections.length ? `${selected} selected result${selected === 1 ? "" : "s"}` : "No result recorded yet";
  document.getElementById("summaryPerformance").textContent = performanceScore === null ? "—" : `${performanceScore}/100`;
  document.getElementById("summaryPerformanceMeta").textContent = studentDashboardState.performance.length ? `${studentDashboardState.performance.length} recorded result${studentDashboardState.performance.length === 1 ? "" : "s"}` : "No performance result yet";
}

// Defines a reusable JavaScript function.
async function initDashboard() {
  // Executes the current JavaScript statement/block.
  currentStudentUser = await requireRole("student");
  // Checks a condition before continuing.
  if (!currentStudentUser) return;

  // Finds an HTML element in the DOM.
  document.getElementById("studentName").textContent = currentStudentUser.name;

  await Promise.all([
    loadProfile(),
    loadSports(),
    loadSchedule(),
    loadSelection(),
    loadTeams(),
    loadPerformance()
  // Executes the current JavaScript statement/block.
  ]);
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function loadProfile() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("student_profile.php");
    // Declares a JavaScript variable.
    const p = data.profile;
    // Finds an HTML element in the DOM.
    document.getElementById("profile").innerHTML = [
      ["Student Code", p.student_code],
      ["Name", p.name],
      ["Phone", p.phone],
      ["Email", p.email],
      ["Age", p.age],
      ["Blood Group", p.blood_group],
      ["Weight", p.weight_kg ? `${p.weight_kg} kg` : "—"],
      ["Height", p.height_cm ? `${p.height_cm} cm` : "—"],
      ["Physical Attributes", p.physical_attributes || "—"]
    ].map(([label, value]) =>
      `<div class="info-item"><small>${esc(label)}</small><strong>${esc(value)}</strong></div>`
    // Executes the current JavaScript statement/block.
    ).join("");
  // Executes the current JavaScript statement/block.
  } catch (error) {
    // Executes the current JavaScript statement/block.
    showMessage("studentMessage", error.message, "error");
  // Executes the current JavaScript statement/block.
  }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function loadSports() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("student_sports.php");
    // Finds an HTML element in the DOM.
    document.getElementById("sports").innerHTML = data.sports.length
      ? data.sports.map(s => `
        <div class="mini-card">
          <strong>${esc(s.sport_name)}</strong>
          <div><span class="tag">${esc(s.position_name || "Position not assigned")}</span></div>
          ${s.handedness ? `<small>Handedness: ${esc(s.handedness)}</small><br>` : ""}
          ${s.serve_type ? `<small>Serve: ${esc(s.serve_type)}</small><br>` : ""}
          ${s.previous_role ? `<small>Previous role: ${esc(s.previous_role)}</small>` : ""}
          ${s.batting_style ? `<small>Batting: ${esc(s.batting_style)}</small><br>` : ""}
          ${s.bowling_style ? `<small>Bowling: ${esc(s.bowling_style)}</small>` : ""}
        </div>
      `).join("")
      // Executes the current JavaScript statement/block.
      : `<p class="muted">No sports registered.</p>`;
  // Executes the current JavaScript statement/block.
  } catch (error) {
    // Executes the current JavaScript statement/block.
    showMessage("studentMessage", error.message, "error");
  // Executes the current JavaScript statement/block.
  }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function loadSchedule() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("student_schedule.php");
    // Keep the API result in memory so the dashboard summary can reuse it.
    studentDashboardState.schedule = data.schedule || [];
    // Build the detailed schedule table from the same API result.
    const rows = studentDashboardState.schedule.map(s => `
      <tr>
        <td>${esc(s.sport_name)}</td>
        <td>${esc(s.session_type)} #${esc(s.session_number)}</td>
        <td>${esc(s.title)}</td>
        <td>${esc(s.session_date)}<br>${esc(s.start_time)}–${esc(s.end_time)}</td>
        <td>${esc(s.location)}</td>
        <td><span class="tag ${s.attendance === "Present" ? "success" : s.attendance === "Absent" ? "danger" : "warning"}">${esc(s.attendance)}</span></td>
        <td>${esc(s.status)}</td>
      </tr>`).join("");

    // Finds an HTML element in the DOM.
    document.getElementById("schedule").innerHTML = rows
      ? `<table><thead><tr><th>Sport</th><th>Type</th><th>Title</th><th>Date / Time</th><th>Location</th><th>Attendance</th><th>Status</th></tr></thead><tbody>${rows}</tbody></table>`
      // Executes the current JavaScript statement/block.
      : `<p class="muted">No sessions found.</p>`;
    // Refresh the summary cards after the schedule has loaded.
    updateStudentSummary();
  // Executes the current JavaScript statement/block.
  } catch (error) {
    // Executes the current JavaScript statement/block.
    showMessage("studentMessage", error.message, "error");
  // Executes the current JavaScript statement/block.
  }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function loadSelection() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("student_selection.php");
    // Keep the API result in memory for the selection summary.
    studentDashboardState.selections = data.selections || [];
    // Render the detailed selection cards.
    document.getElementById("selection").innerHTML = studentDashboardState.selections.length
      ? data.selections.map(s => `
        <div class="mini-card">
          <strong>${esc(s.sport_name)}</strong>
          <span class="tag ${s.selection_status === "Selected" ? "success" : s.selection_status === "Not Selected" ? "danger" : "warning"}">${esc(s.selection_status)}</span>
          <p><strong>Score:</strong> ${esc(s.overall_score)} / 100</p>
          <p><strong>Testing:</strong> ${esc(s.testing_session_title || "—")} (${esc(s.testing_date || "—")})</p>
          <p>${esc(s.coach_notes || "No coach notes.")}</p>
        </div>
      `).join("")
      // Executes the current JavaScript statement/block.
      : `<p class="muted">No selection results recorded yet.</p>`;
    // Refresh the summary cards after selection data has loaded.
    updateStudentSummary();
  // Executes the current JavaScript statement/block.
  } catch (error) {
    // Executes the current JavaScript statement/block.
    showMessage("studentMessage", error.message, "error");
  // Executes the current JavaScript statement/block.
  }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function loadTeams() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("student_team.php");
    // Finds an HTML element in the DOM.
    document.getElementById("teams").innerHTML = data.teams.length
      ? data.teams.map(t => `
        <div class="mini-card">
          <strong>${esc(t.team_name)}</strong>
          <span class="tag">${esc(t.sport_name)}</span>
          <span class="tag ${t.lineup_status === "Starter" ? "success" : "warning"}">${esc(t.lineup_status)}</span>
          <p>Position: <strong>${esc(t.position_name || "—")}</strong></p>
          <p>Jersey: <strong>${esc(t.jersey_number || "—")}</strong> | Slot: <strong>${esc(t.formation_slot || "—")}</strong></p>
          <p>Formation: ${esc(t.formation || "—")}</p>
        </div>
      `).join("")
      // Executes the current JavaScript statement/block.
      : `<p class="muted">No team assignment yet.</p>`;
  // Executes the current JavaScript statement/block.
  } catch (error) {
    // Executes the current JavaScript statement/block.
    showMessage("studentMessage", error.message, "error");
  // Executes the current JavaScript statement/block.
  }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
function performanceTable(title, rows, columns) {
  // Checks a condition before continuing.
  if (!rows.length) return `<div class="mini-card"><strong>${title}</strong><p class="muted">No recorded results.</p></div>`;
  // Returns a value from the current function.
  return `
    <div class="mini-card">
      <h3>${title}</h3>
      <div class="table-wrap">
        <table>
          <thead><tr>${columns.map(c => `<th>${esc(c.label)}</th>`).join("")}</tr></thead>
          // Executes the current JavaScript statement/block.
          <tbody>${rows.map(r => `<tr>${columns.map(c => {
        // Declares a JavaScript variable.
        const raw = r[c.key];
        // Declares a JavaScript variable.
        const num = Number(raw);
        // Declares a JavaScript variable.
        let cls = "";
        // Checks a condition before continuing.
        if (Number.isFinite(num)) {
          // Checks a condition before continuing.
          if (["overall_score","passing_accuracy","shot_accuracy","reception_success_rate"].includes(c.key)) {
            // Executes the current JavaScript statement/block.
            cls = num >= 80 ? "metric-good" : num >= 60 ? "metric-info" : num >= 40 ? "metric-warn" : "metric-bad";
          // Checks a condition before continuing.
          } else if (["goals","assists","service_aces","successful_attacks","blocks","digs","runs","wickets","catches"].includes(c.key)) {
            // Executes the current JavaScript statement/block.
            cls = num > 0 ? "metric-good" : "";
          // Checks a condition before continuing.
          } else if (["fouls","yellow_cards","red_cards","service_errors","rule_violations","runs_conceded"].includes(c.key)) {
            // Executes the current JavaScript statement/block.
            cls = num > 0 ? "metric-warn" : "metric-good";
          // Executes the current JavaScript statement/block.
          }
        // Executes the current JavaScript statement/block.
        }
        // Returns a value from the current function.
        return `<td class="${cls}">${esc(raw)}</td>`;
      // Executes the current JavaScript statement/block.
      }).join("")}</tr>`).join("")}</tbody>
        </table>
      </div
    </div>`;
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function loadPerformance() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("student_performance.php");
    // Declares a JavaScript variable.
    const p = data.performance;
    // Combine the sport-specific result arrays for the overall summary.
    studentDashboardState.performance = [
      ...(p.football || []),
      ...(p.volleyball || []),
      ...(p.cricket || [])
    ];

    // Finds an HTML element in the DOM.
    document.getElementById("performance").innerHTML =
      performanceTable("Football", p.football, [
        { key: "session_type", label: "Type" },
        { key: "title", label: "Session" },
        { key: "session_date", label: "Date" },
        { key: "passing_accuracy", label: "Pass %" },
        { key: "shot_accuracy", label: "Shot %" },
        { key: "speed_kmh", label: "Speed" },
        { key: "goals", label: "Goals" },
        { key: "assists", label: "Assists" },
        { key: "fouls", label: "Fouls" },
        { key: "yellow_cards", label: "Yellow" },
        { key: "red_cards", label: "Red" },
        { key: "overall_score", label: "Score" }
      ]) +
      performanceTable("Volleyball", p.volleyball, [
        { key: "session_type", label: "Type" },
        { key: "title", label: "Session" },
        { key: "session_date", label: "Date" },
        { key: "service_aces", label: "Aces" },
        { key: "service_errors", label: "Serve Errors" },
        { key: "successful_attacks", label: "Kills" },
        { key: "blocks", label: "Blocks" },
        { key: "digs", label: "Digs" },
        { key: "assists", label: "Assists" },
        { key: "reception_success_rate", label: "Reception %" },
        { key: "overall_score", label: "Score" }
      ]) +
      performanceTable("Cricket", p.cricket, [
        { key: "session_type", label: "Type" },
        { key: "title", label: "Session" },
        { key: "session_date", label: "Date" },
        { key: "runs", label: "Runs" },
        { key: "balls_faced", label: "Balls" },
        { key: "fours", label: "4s" },
        { key: "sixes", label: "6s" },
        { key: "wickets", label: "Wickets" },
        { key: "runs_conceded", label: "Runs Conceded" },
        { key: "catches", label: "Catches" },
        { key: "rule_violations", label: "Violations" },
        { key: "overall_score", label: "Score" }
      ]) +
      // Executes the current JavaScript statement/block.
      `<div class="mini-card"><strong>Coach Notes</strong><p class="muted">Open each result above through the database records; notes are also returned by the API.</p></div>`;
    // Refresh the summary cards after performance data has loaded.
    updateStudentSummary();
  // Executes the current JavaScript statement/block.
  } catch (error) {
    // Executes the current JavaScript statement/block.
    showMessage("studentMessage", error.message, "error");
  // Executes the current JavaScript statement/block.
  }
// Executes the current JavaScript statement/block.
}


/* SEPARATE AUTH PORTALS */
async function doLogin(identifier, password, messageId) {
  // Executes the current JavaScript statement/block.
  hideMessage(messageId);
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("login.php", {method:"POST", body:JSON.stringify({identifier,password})});
    // Navigates the browser to another page.
    window.location.href = data.redirect;
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage(messageId, error.message, "error"); }
// Executes the current JavaScript statement/block.
}
// Defines a reusable JavaScript function.
function initStudentLogin() {
  // Attaches an event handler.
  document.getElementById("studentLoginForm")?.addEventListener("submit", e => {
    // Executes the current JavaScript statement/block.
    e.preventDefault();
    // Finds an HTML element in the DOM.
    doLogin(document.getElementById("student_identifier").value.trim(), document.getElementById("student_password").value, "studentLoginMessage");
  // Executes the current JavaScript statement/block.
  });
// Executes the current JavaScript statement/block.
}
// Defines a reusable JavaScript function.
function initAdminLogin() {
  // Attaches an event handler.
  document.getElementById("adminLoginForm")?.addEventListener("submit", e => {
    // Executes the current JavaScript statement/block.
    e.preventDefault();
    // Finds an HTML element in the DOM.
    doLogin(document.getElementById("admin_identifier").value.trim(), document.getElementById("admin_password").value, "adminLoginMessage");
  // Executes the current JavaScript statement/block.
  });
// Executes the current JavaScript statement/block.
}
// Defines a reusable JavaScript function.
async function loadRegistrationOptions() {
  // Declares a JavaScript variable.
  const data = await api("registration_options.php");
  // Returns a value from the current function.
  return {sports:data.sports || [], positions:data.positions || []};
// Executes the current JavaScript statement/block.
}
// Defines a reusable JavaScript function.
function positionSelect(sportId, positions, name) {
  // Declares a JavaScript variable.
  const list = positions.filter(p => Number(p.sport_id) === Number(sportId));
  // Returns a value from the current function.
  return `<label>Position / Role</label><select name="${name}" required><option value="">Choose position</option>${list.map(p=>`<option value="${esc(p.position_id)}">${esc(p.position_name)}</option>`).join("")}</select>`;
// Executes the current JavaScript statement/block.
}
// Defines a reusable JavaScript function.
async function initStudentRegistration() {
  // Declares a JavaScript variable.
  const box=document.getElementById("studentSportChoices");
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const {sports,positions}=await loadRegistrationOptions();
    // Executes the current JavaScript statement/block.
    box.innerHTML=sports.map(sp=>`<div class="mini-card sport-registration-card"><label><input type="checkbox" class="student-sport" value="${sp.sport_id}"> <strong>${esc(sp.sport_name)}</strong></label><div id="studentPosition_${sp.sport_id}" class="stack hidden"></div></div>`).join("");
    // Attaches an event handler.
    box.querySelectorAll(".student-sport").forEach(cb=>cb.addEventListener("change",()=>{
      // Declares a JavaScript variable.
      const holder=document.getElementById(`studentPosition_${cb.value}`);
      // Changes CSS classes on an HTML element.
      holder.classList.toggle("hidden",!cb.checked);
      // Reads a form control's current value.
      holder.innerHTML=cb.checked ? positionSelect(cb.value,positions,`position_${cb.value}`) : "";
    // Executes the current JavaScript statement/block.
    }));
    // Attaches an event handler.
    document.getElementById("studentRegistrationForm")?.addEventListener("submit", async e=>{
      // Executes the current JavaScript statement/block.
      e.preventDefault(); hideMessage("studentRegistrationMessage");
      // Declares a JavaScript variable.
      const form=new FormData(e.currentTarget), selected=[...box.querySelectorAll(".student-sport:checked")];
      // Checks a condition before continuing.
      if(!selected.length){showMessage("studentRegistrationMessage","Select at least one sport.","error");return;}
      // Declares a JavaScript variable.
      const sportsPayload=selected.map(cb=>({sport_id:Number(cb.value),position_id:Number(form.get(`position_${cb.value}`))}));
      // Checks a condition before continuing.
      if(sportsPayload.some(x=>!x.position_id)){showMessage("studentRegistrationMessage","Choose a position for every selected sport.","error");return;}
      // Declares a JavaScript variable.
      const payload=Object.fromEntries(form.entries()); payload.sports=sportsPayload; delete payload.sport_id; delete payload.position_id;
      // Sends a request to the backend.
      try { const data=await api("register.php",{method:"POST",body:JSON.stringify(payload)}); window.location.href=data.redirect || "portal.html"; }
      // Handles an error from the try block.
      catch(error){showMessage("studentRegistrationMessage",error.message,"error");}
    // Executes the current JavaScript statement/block.
    });
  // Executes the current JavaScript statement/block.
  } catch(error){showMessage("studentRegistrationMessage",error.message,"error");}
// Executes the current JavaScript statement/block.
}
// Defines a reusable JavaScript function.
async function initAdminRegistration() {
  // Declares a JavaScript variable.
  const box=document.getElementById("adminSportChoices");
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const {sports}=await loadRegistrationOptions();
    // Executes the current JavaScript statement/block.
    box.innerHTML=sports.map(sp=>`<label class="mini-card"><input type="checkbox" name="sport_ids[]" value="${sp.sport_id}"> <strong>${esc(sp.sport_name)}</strong></label>`).join("");
    // Attaches an event handler.
    document.getElementById("adminRegistrationForm")?.addEventListener("submit",async e=>{
      // Executes the current JavaScript statement/block.
      e.preventDefault(); hideMessage("adminRegistrationMessage");
      // Declares a JavaScript variable.
      const form=new FormData(e.currentTarget), ids=[...box.querySelectorAll("input:checked")].map(x=>Number(x.value));
      // Checks a condition before continuing.
      if(!ids.length){showMessage("adminRegistrationMessage","Select at least one sport to manage.","error");return;}
      // Declares a JavaScript variable.
      const payload=Object.fromEntries(form.entries()); payload.sport_ids=ids; delete payload["sport_ids[]"];
      // Sends a request to the backend.
      try { const data=await api("coach_register.php",{method:"POST",body:JSON.stringify(payload)}); window.location.href=data.redirect || "coach.html"; }
      // Handles an error from the try block.
      catch(error){showMessage("adminRegistrationMessage",error.message,"error");}
    // Executes the current JavaScript statement/block.
    });
  // Executes the current JavaScript statement/block.
  } catch(error){showMessage("adminRegistrationMessage",error.message,"error");}
// Executes the current JavaScript statement/block.
}

/* Legacy registration page compatibility: redirect it to the new multi-sport student registration. */
if (location.pathname.endsWith("/register.html") || location.pathname.endsWith("\\register.html")) {
  // Executes the current JavaScript statement/block.
  location.replace("student_register.html");
// Executes the current JavaScript statement/block.
}

/* COACH DASHBOARD */
async function initCoach() {
  // Declares a JavaScript variable.
  const user = await requireRole("coach");
  // Checks a condition before continuing.
  if (!user) return;

  // Finds an HTML element in the DOM.
  document.getElementById("coachName").textContent = user.name;

  if (window.initCoachV3) window.initCoachV3();
  await Promise.all([
    loadCoachStudents(),
    loadCoachCoaches(),
    loadCoachSessions(),
    initializePerformanceEditor(),
    loadCoachSelections(),
    loadCoachTeams()
  // Executes the current JavaScript statement/block.
  ]);
  if (window.refreshCoachV3) window.refreshCoachV3();
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function loadCoachStudents() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("coach_students.php");
    // Finds an HTML element in the DOM.
    document.getElementById("studentCount").textContent = data.students.length;
    // Finds an HTML element in the DOM.
    document.getElementById("studentsTable").innerHTML = `
      <table><thead><tr><th>ID</th><th>Code</th><th>Name</th><th>Contact</th><th>Age</th><th>Actions</th></tr></thead>
      <tbody>${data.students.map(s => `
        <tr>
          <td>${esc(s.student_id)}</td><td>${esc(s.student_code)}</td><td>${esc(s.name)}</td>
          <td>${esc(s.phone)}<br>${esc(s.email)}</td><td>${esc(s.age)}</td>
          <td>
            <button class="btn small" onclick='editStudent(${JSON.stringify(s)})'>Edit</button>
            <button class="btn small" onclick="deleteStudent(${s.student_id})">Delete</button>
          </td>
        </tr>`).join("")}</tbody></table>`;
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
function editStudent(s) {
  // Finds an HTML element in the DOM.
  document.getElementById("student_id").value = s.student_id;
  // Finds an HTML element in the DOM.
  document.getElementById("student_code").value = s.student_code;
  // Finds an HTML element in the DOM.
  document.getElementById("student_name").value = s.name;
  // Finds an HTML element in the DOM.
  document.getElementById("student_phone").value = s.phone;
  // Finds an HTML element in the DOM.
  document.getElementById("student_email").value = s.email;
  // Finds an HTML element in the DOM.
  document.getElementById("student_age").value = s.age;
  // Finds an HTML element in the DOM.
  document.getElementById("student_blood_group").value = s.blood_group;
  // Finds an HTML element in the DOM.
  document.getElementById("student_weight").value = s.weight_kg ?? "";
  // Finds an HTML element in the DOM.
  document.getElementById("student_height").value = s.height_cm ?? "";
  // Finds an HTML element in the DOM.
  document.getElementById("student_attributes").value = s.physical_attributes ?? "";
  // Finds an HTML element in the DOM.
  window.scrollTo({ top: document.getElementById("studentForm").offsetTop - 90, behavior: "smooth" });
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
function clearStudentForm() {
  // Finds an HTML element in the DOM.
  document.getElementById("studentForm").reset();
  // Finds an HTML element in the DOM.
  document.getElementById("student_id").value = "";
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function deleteStudent(id) {
  // Checks a condition before continuing.
  if (!confirm("Delete this student? Related database records may also be affected by foreign-key rules.")) return;
  // Starts error-handling logic.
  try {
    // Sends a request to the backend.
    await api(`coach_students.php?student_id=${id}`, { method: "DELETE" });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", "Student deleted.");
    // Executes the current JavaScript statement/block.
    loadCoachStudents();
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Attaches an event handler.
document.getElementById("studentForm")?.addEventListener("submit", async event => {
  // Executes the current JavaScript statement/block.
  event.preventDefault();
  // Declares a JavaScript variable.
  const id = document.getElementById("student_id").value;
  // Declares a JavaScript variable.
  const payload = {
    // Finds an HTML element in the DOM.
    student_code: document.getElementById("student_code").value,
    // Finds an HTML element in the DOM.
    name: document.getElementById("student_name").value,
    // Finds an HTML element in the DOM.
    phone: document.getElementById("student_phone").value,
    // Finds an HTML element in the DOM.
    email: document.getElementById("student_email").value,
    // Finds an HTML element in the DOM.
    password: document.getElementById("student_password").value,
    // Finds an HTML element in the DOM.
    age: Number(document.getElementById("student_age").value),
    // Finds an HTML element in the DOM.
    blood_group: document.getElementById("student_blood_group").value,
    // Finds an HTML element in the DOM.
    weight_kg: document.getElementById("student_weight").value,
    // Finds an HTML element in the DOM.
    height_cm: document.getElementById("student_height").value,
    // Finds an HTML element in the DOM.
    physical_attributes: document.getElementById("student_attributes").value
  // Executes the current JavaScript statement/block.
  };
  // Checks a condition before continuing.
  if (!payload.password) delete payload.password;

  // Starts error-handling logic.
  try {
    // Sends a request to the backend.
    await api("coach_students.php", {
      method: id ? "PUT" : "POST",
      // Converts JavaScript data to JSON text.
      body: JSON.stringify(id ? { ...payload, student_id: Number(id) } : payload)
    // Executes the current JavaScript statement/block.
    });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", id ? "Student updated." : "Student added.");
    // Executes the current JavaScript statement/block.
    clearStudentForm();
    // Executes the current JavaScript statement/block.
    loadCoachStudents();
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
});

// Defines a reusable JavaScript function.
async function loadCoachCoaches() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("coach_coaches.php");
    // Finds an HTML element in the DOM.
    document.getElementById("coachCount").textContent = data.coaches.length;
    // Finds an HTML element in the DOM.
    document.getElementById("coachesTable").innerHTML = `
      <table><thead><tr><th>ID</th><th>Code</th><th>Name</th><th>Contact</th><th>Experience</th><th>Actions</th></tr></thead>
      <tbody>${data.coaches.map(c => `
        <tr>
          <td>${esc(c.coach_id)}</td><td>${esc(c.coach_code)}</td><td>${esc(c.name)}</td>
          <td>${esc(c.phone)}<br>${esc(c.email)}</td><td>${esc(c.experience_years)} years</td>
          <td><button class="btn small" onclick='editCoach(${JSON.stringify(c)})'>Edit</button>
          <button class="btn small" onclick="deleteCoach(${c.coach_id})">Delete</button></td>
        </tr>`).join("")}</tbody></table>`;
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
function editCoach(c) {
  // Finds an HTML element in the DOM.
  document.getElementById("coach_id").value = c.coach_id;
  // Finds an HTML element in the DOM.
  document.getElementById("coach_code").value = c.coach_code;
  // Finds an HTML element in the DOM.
  document.getElementById("coach_name").value = c.name;
  // Finds an HTML element in the DOM.
  document.getElementById("coach_phone").value = c.phone;
  // Finds an HTML element in the DOM.
  document.getElementById("coach_email").value = c.email;
  // Finds an HTML element in the DOM.
  document.getElementById("coach_age").value = c.age;
  // Finds an HTML element in the DOM.
  document.getElementById("coach_blood_group").value = c.blood_group;
  // Finds an HTML element in the DOM.
  document.getElementById("coach_weight").value = c.weight_kg ?? "";
  // Finds an HTML element in the DOM.
  document.getElementById("coach_height").value = c.height_cm ?? "";
  // Finds an HTML element in the DOM.
  document.getElementById("coach_experience").value = c.experience_years ?? "";
  // Finds an HTML element in the DOM.
  document.getElementById("coach_attributes").value = c.physical_attributes ?? "";
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
function clearCoachForm() {
  // Finds an HTML element in the DOM.
  document.getElementById("coachForm").reset();
  // Finds an HTML element in the DOM.
  document.getElementById("coach_id").value = "";
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function deleteCoach(id) {
  // Checks a condition before continuing.
  if (!confirm("Delete this coach?")) return;
  // Starts error-handling logic.
  try {
    // Sends a request to the backend.
    await api(`coach_coaches.php?coach_id=${id}`, { method: "DELETE" });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", "Coach deleted.");
    // Executes the current JavaScript statement/block.
    loadCoachCoaches();
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Attaches an event handler.
document.getElementById("coachForm")?.addEventListener("submit", async event => {
  // Executes the current JavaScript statement/block.
  event.preventDefault();
  // Declares a JavaScript variable.
  const id = document.getElementById("coach_id").value;
  // Declares a JavaScript variable.
  const payload = {
    // Finds an HTML element in the DOM.
    coach_code: document.getElementById("coach_code").value,
    // Finds an HTML element in the DOM.
    name: document.getElementById("coach_name").value,
    // Finds an HTML element in the DOM.
    phone: document.getElementById("coach_phone").value,
    // Finds an HTML element in the DOM.
    email: document.getElementById("coach_email").value,
    // Finds an HTML element in the DOM.
    password: document.getElementById("coach_password").value,
    // Finds an HTML element in the DOM.
    age: Number(document.getElementById("coach_age").value),
    // Finds an HTML element in the DOM.
    blood_group: document.getElementById("coach_blood_group").value,
    // Finds an HTML element in the DOM.
    weight_kg: document.getElementById("coach_weight").value,
    // Finds an HTML element in the DOM.
    height_cm: document.getElementById("coach_height").value,
    // Finds an HTML element in the DOM.
    experience_years: Number(document.getElementById("coach_experience").value || 0),
    // Finds an HTML element in the DOM.
    physical_attributes: document.getElementById("coach_attributes").value
  // Executes the current JavaScript statement/block.
  };
  // Checks a condition before continuing.
  if (!payload.password) delete payload.password;

  // Starts error-handling logic.
  try {
    // Sends a request to the backend.
    await api("coach_coaches.php", {
      method: id ? "PUT" : "POST",
      // Converts JavaScript data to JSON text.
      body: JSON.stringify(id ? { ...payload, coach_id: Number(id) } : payload)
    // Executes the current JavaScript statement/block.
    });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", id ? "Coach updated." : "Coach added.");
    // Executes the current JavaScript statement/block.
    clearCoachForm();
    // Executes the current JavaScript statement/block.
    loadCoachCoaches();
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
});

// Defines a reusable JavaScript function.
async function loadCoachSessions() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("coach_sessions.php");
    // Finds an HTML element in the DOM.
    document.getElementById("sessionCount").textContent = data.sessions.length;
    // Finds an HTML element in the DOM.
    document.getElementById("sessionsTable").innerHTML = `
      <table><thead><tr><th>ID</th><th>Sport</th><th>Type</th><th>Title</th><th>Date/Time</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>${data.sessions.map(s => `
        <tr>
          <td>${esc(s.session_id)}</td><td>${esc(s.sport_name)}</td>
          <td>${esc(s.session_type)} #${esc(s.session_number)}</td><td>${esc(s.title)}</td>
          <td>${esc(s.session_date)}<br>${esc(s.start_time)}–${esc(s.end_time)}</td>
          <td>${esc(s.status)}</td>
          <td><button class="btn small" onclick='editSession(${JSON.stringify(s)})'>Edit</button>
          <button class="btn small" onclick="deleteSession(${s.session_id})">Delete</button></td>
        </tr>`).join("")}</tbody></table>`;
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
function editSession(s) {
  // Finds an HTML element in the DOM.
  document.getElementById("session_id").value = s.session_id;
  // Finds an HTML element in the DOM.
  document.getElementById("session_sport").value = s.sport_id;
  // Finds an HTML element in the DOM.
  document.getElementById("session_type").value = s.session_type;
  // Finds an HTML element in the DOM.
  document.getElementById("session_number").value = s.session_number;
  // Finds an HTML element in the DOM.
  document.getElementById("session_title").value = s.title;
  // Finds an HTML element in the DOM.
  document.getElementById("session_date").value = s.session_date;
  // Finds an HTML element in the DOM.
  document.getElementById("session_start").value = s.start_time;
  // Finds an HTML element in the DOM.
  document.getElementById("session_end").value = s.end_time;
  // Finds an HTML element in the DOM.
  document.getElementById("session_location").value = s.location || "";
  // Finds an HTML element in the DOM.
  document.getElementById("session_description").value = s.description || "";
  // Finds an HTML element in the DOM.
  document.getElementById("session_status").value = s.status;
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
function clearSessionForm() {
  // Finds an HTML element in the DOM.
  document.getElementById("sessionForm").reset();
  // Finds an HTML element in the DOM.
  document.getElementById("session_id").value = "";
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function deleteSession(id) {
  // Checks a condition before continuing.
  if (!confirm("Delete this session?")) return;
  // Starts error-handling logic.
  try {
    // Sends a request to the backend.
    await api(`coach_sessions.php?session_id=${id}`, { method: "DELETE" });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", "Session deleted.");
    // Executes the current JavaScript statement/block.
    loadCoachSessions();
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Attaches an event handler.
document.getElementById("sessionForm")?.addEventListener("submit", async event => {
  // Executes the current JavaScript statement/block.
  event.preventDefault();
  // Declares a JavaScript variable.
  const id = document.getElementById("session_id").value;
  // Declares a JavaScript variable.
  const payload = {
    // Finds an HTML element in the DOM.
    sport_id: Number(document.getElementById("session_sport").value),
    // Finds an HTML element in the DOM.
    session_type: document.getElementById("session_type").value,
    // Finds an HTML element in the DOM.
    session_number: Number(document.getElementById("session_number").value),
    // Finds an HTML element in the DOM.
    title: document.getElementById("session_title").value,
    // Finds an HTML element in the DOM.
    session_date: document.getElementById("session_date").value,
    // Finds an HTML element in the DOM.
    start_time: document.getElementById("session_start").value,
    // Finds an HTML element in the DOM.
    end_time: document.getElementById("session_end").value,
    // Finds an HTML element in the DOM.
    location: document.getElementById("session_location").value,
    // Finds an HTML element in the DOM.
    description: document.getElementById("session_description").value,
    // Finds an HTML element in the DOM.
    status: document.getElementById("session_status").value
  // Executes the current JavaScript statement/block.
  };

  // Starts error-handling logic.
  try {
    // Sends a request to the backend.
    await api("coach_sessions.php", {
      method: id ? "PUT" : "POST",
      // Converts JavaScript data to JSON text.
      body: JSON.stringify(id ? { ...payload, session_id: Number(id) } : payload)
    // Executes the current JavaScript statement/block.
    });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", id ? "Session updated." : "Session created.");
    // Executes the current JavaScript statement/block.
    clearSessionForm();
    // Executes the current JavaScript statement/block.
    loadCoachSessions();
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
});

// Defines a reusable JavaScript function.
async function loadAttendance() {
  // Declares a JavaScript variable.
  const sessionId = Number(document.getElementById("attendanceSessionId").value);
  // Checks a condition before continuing.
  if (!sessionId) return showMessage("coachMessage", "Enter a session ID.", "error");

  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api(`coach_attendance.php?session_id=${sessionId}`);
    // Finds an HTML element in the DOM.
    document.getElementById("attendanceBulkActions").hidden = false;
    document.getElementById("attendanceTable").innerHTML = `
      <table><thead><tr><th>Student</th><th>Code</th><th>Contact</th><th>Attendance</th><th>Save</th></tr></thead>
      <tbody>${data.participants.map(p => `
        <tr>
          <td>${esc(p.name)}</td><td>${esc(p.student_code)}</td><td>${esc(p.phone)}</td>
          <td><select id="att-${p.student_id}">
            ${["Present","Absent","Pending"].map(a => `<option ${a === p.attendance ? "selected" : ""}>${a}</option>`).join("")}
          </select></td>
          <td><button class="btn small" onclick="saveAttendance(${sessionId}, ${p.student_id})">Save</button></td>
        </tr>`).join("")}</tbody></table>`;
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function saveAttendance(sessionId, studentId) {
  // Starts error-handling logic.
  try {
    // Sends a request to the backend.
    await api("coach_attendance.php", {
      method: "POST",
      // Converts JavaScript data to JSON text.
      body: JSON.stringify({
        session_id: sessionId,
        student_id: studentId,
        // Finds an HTML element in the DOM.
        attendance: document.getElementById(`att-${studentId}`).value
      // Executes the current JavaScript statement/block.
      })
    // Executes the current JavaScript statement/block.
    });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", "Attendance saved.");
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
// User-friendly performance editor. JSON is created internally for the existing PHP API.
const PERFORMANCE_METRICS = {
  Football: ["passing_accuracy","shot_accuracy","speed_kmh","goals","assists","tackles","interceptions","fouls","yellow_cards","red_cards","saves","goals_conceded","save_accuracy"],
  Volleyball: ["service_aces","service_errors","attack_attempts","successful_attacks","attack_errors","blocks","digs","assists","reception_success_rate","net_violations","rotation_faults","foot_faults","double_contacts","other_faults"],
  Cricket: ["runs","balls_faced","fours","sixes","dismissals","overs","runs_conceded","wickets","maidens","wides","no_balls","catches","run_outs","stumpings","fielding_errors","fitness_score","experience_score","rule_violations"]
};
const PERFORMANCE_PERCENT = new Set(["passing_accuracy","shot_accuracy","save_accuracy","reception_success_rate","fitness_score","experience_score"]);
const PERFORMANCE_DECIMAL = new Set(["speed_kmh","overs",...PERFORMANCE_PERCENT]);
let performanceSessions = [];
let performanceRows = [];
let performanceEditingId = null;
function performanceSport() {
  const session = performanceSessions.find(s => String(s.session_id) === document.getElementById("performanceSessionId").value);
  return session ? session.sport_name : "";
}
function performanceLabel(field) { return field.split("_").map(w => w[0].toUpperCase()+w.slice(1)).join(" ") + (PERFORMANCE_PERCENT.has(field) ? " (%)" : field === "speed_kmh" ? " (km/h)" : ""); }
async function initializePerformanceEditor() {
  const sessionSelect = document.getElementById("performanceSessionId");
  if (!sessionSelect) return;
  try {
    const data = await api("coach_performance_options.php");
    performanceSessions = data.sessions;
    sessionSelect.innerHTML = '<option value="">Choose a practice…</option>' + performanceSessions.map(s => `<option value="${Number(s.session_id)}">${esc(s.sport_name)} · ${esc(s.title)} · ${esc(s.session_date)} (${esc(s.coach_name)})</option>`).join("");
    if (!performanceSessions.length) document.getElementById("performanceSportLabel").textContent = "No available practice sessions. Create one in Sessions first.";
  } catch(e) { showMessage("coachMessage", e.message, "error"); }
}
async function performanceSessionChanged() {
  const sessionId = Number(document.getElementById("performanceSessionId").value);
  const sport = performanceSport();
  const studentSelect = document.getElementById("performanceStudentId");
  performanceEditingId = null;
  performanceRows = [];
  studentSelect.innerHTML = '<option value="">Choose a player…</option>';
  document.getElementById("performanceSportLabel").textContent = sport ? `${sport} statistics · fields marked optional may be left blank` : "Select a practice to begin.";
  document.getElementById("performanceFields").innerHTML = sport ? PERFORMANCE_METRICS[sport].map(f => `<label>${performanceLabel(f)} <input data-metric="${f}" type="number" min="0" ${PERFORMANCE_PERCENT.has(f) ? 'max="100"' : f === "speed_kmh" ? 'max="200"' : ''} step="${PERFORMANCE_DECIMAL.has(f) ? '0.01' : '1'}" placeholder="Optional"></label>`).join("") : "";
  resetPerformanceEditor();
  if (!sessionId) { document.getElementById("coachPerformanceTable").innerHTML = ""; return; }
  try {
    const [participants] = await Promise.all([api(`coach_attendance.php?session_id=${sessionId}`), loadPerformanceForCoach()]);
    studentSelect.innerHTML = '<option value="">Choose a player…</option>' + participants.participants.map(p => `<option value="${Number(p.student_id)}">${esc(p.name)} (${esc(p.student_code)})</option>`).join("");
  } catch(e) { showMessage("coachMessage", e.message, "error"); }
}
function resetPerformanceEditor() {
  performanceEditingId = null;
  const form = document.getElementById("performanceForm");
  if (form) form.reset();
  const button = document.getElementById("performanceSaveButton");
  if (button) button.textContent = "Save performance";
}
function performancePlayerChanged() {
  const studentId = Number(document.getElementById("performanceStudentId").value);
  const record = performanceRows.find(r => Number(r.student_id) === studentId);
  resetPerformanceEditor();
  if (!record) return;
  performanceEditingId = Number(record.performance_id);
  document.querySelectorAll("#performanceFields [data-metric]").forEach(el => { el.value = record[el.dataset.metric] ?? ""; });
  document.getElementById("performanceOverallScore").value = record.overall_score ?? "";
  document.getElementById("performanceCoachNotes").value = record.coach_notes ?? "";
  document.getElementById("performanceSaveButton").textContent = "Update performance";
}
async function loadPerformanceForCoach() {
  const sessionId = Number(document.getElementById("performanceSessionId").value);
  const sport = performanceSport();
  if (!sessionId || !sport) return;
  try {
    const data = await api(`coach_performance.php?session_id=${sessionId}&sport=${encodeURIComponent(sport)}`);
    performanceRows = data.performance || [];
    document.getElementById("coachPerformanceTable").innerHTML = performanceRows.length
      ? `<table><thead><tr><th>Player</th><th>Score</th><th>Coach's notes</th><th>Action</th></tr></thead><tbody>${performanceRows.map(r => `<tr><td>${esc(r.name)} (${esc(r.student_code)})</td><td>${esc(r.overall_score)}</td><td>${esc(r.coach_notes || "—")}</td><td><button class="btn small" type="button" onclick="editPerformanceRecord(${Number(r.student_id)})">Edit</button></td></tr>`).join("")}</tbody></table>`
      : '<p class="muted">No records yet for this practice.</p>';
  } catch(e) { showMessage("coachMessage", e.message, "error"); }
}
function editPerformanceRecord(studentId) {
  document.getElementById("performanceStudentId").value = String(studentId);
  performancePlayerChanged();
  document.getElementById("performanceForm").scrollIntoView({behavior:"smooth",block:"center"});
}
async function savePerformance() {
  const sessionId = Number(document.getElementById("performanceSessionId").value);
  const studentId = Number(document.getElementById("performanceStudentId").value);
  const sport = performanceSport();
  const form = document.getElementById("performanceForm");
  if (!sessionId || !studentId || !sport) return showMessage("coachMessage", "Choose a practice and a player first.", "error");
  if (!form.reportValidity()) return;
  const payload = {sport,session_id:sessionId,student_id:studentId,overall_score:Number(document.getElementById("performanceOverallScore").value),coach_notes:document.getElementById("performanceCoachNotes").value.trim()};
  document.querySelectorAll("#performanceFields [data-metric]").forEach(el => { if (el.value !== "") payload[el.dataset.metric] = Number(el.value); });
  if (performanceEditingId) payload.performance_id = performanceEditingId;
  const button = document.getElementById("performanceSaveButton");
  button.disabled = true;
  try {
    const result = await api("coach_performance.php",{method:performanceEditingId ? "PUT" : "POST",body:JSON.stringify(payload)});
    showMessage("coachMessage",result.message);
    await loadPerformanceForCoach();
    if (document.getElementById("performanceNextPlayer")?.checked && !performanceEditingId) {
      const select = document.getElementById("performanceStudentId");
      const current = select.selectedIndex;
      if (current < select.options.length - 1) select.selectedIndex = current + 1;
    }
    performancePlayerChanged();
    if (window.loadCoachAnalytics) loadCoachAnalytics();
  } catch(e) { showMessage("coachMessage",e.message,"error"); }
  finally { button.disabled = false; }
}

async function loadCoachSelections() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("coach_selection.php");
    // Finds an HTML element in the DOM.
    document.getElementById("selectionCount").textContent = data.selections.length;
    // Finds an HTML element in the DOM.
    document.getElementById("selectionTable").innerHTML = `
      <table><thead><tr><th>Student</th><th>Sport</th><th>Score</th><th>Status</th><th>Notes</th></tr></thead>
      <tbody>${data.selections.map(s => `
        <tr><td>${esc(s.name)} (${esc(s.student_code)})</td><td>${esc(s.sport_name)}</td>
        <td>${esc(s.overall_score)}</td>
        <td><span class="tag ${s.selection_status === "Selected" ? "success" : "danger"}">${esc(s.selection_status)}</span></td>
        // Executes the current JavaScript statement/block.
        <td>${esc(s.coach_notes || "")}</td></tr>`).join("")}</tbody></table>`;
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function saveSelection() {
  // Declares a JavaScript variable.
  const payload = {
    // Finds an HTML element in the DOM.
    student_id: Number(document.getElementById("selectionStudentId").value),
    // Finds an HTML element in the DOM.
    sport_id: Number(document.getElementById("selectionSportId").value),
    // Finds an HTML element in the DOM.
    testing_session_id: Number(document.getElementById("selectionTestingSessionId").value),
    // Finds an HTML element in the DOM.
    overall_score: Number(document.getElementById("selectionScore").value),
    // Finds an HTML element in the DOM.
    coach_notes: document.getElementById("selectionNotes").value
  // Executes the current JavaScript statement/block.
  };

  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("coach_selection.php", {
      method: "POST",
      // Converts JavaScript data to JSON text.
      body: JSON.stringify(payload)
    // Executes the current JavaScript statement/block.
    });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", `${data.message} Result: ${data.selection_status}`);
    // Executes the current JavaScript statement/block.
    loadCoachSelections();
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function loadCoachTeams() {
  // Starts error-handling logic.
  try {
    // Declares a JavaScript variable.
    const data = await api("coach_team.php");
    // Finds an HTML element in the DOM.
    document.getElementById("teamsTable").innerHTML = `
      <table><thead><tr><th>Team</th><th>Sport</th><th>Formation</th><th>Player</th><th>Position</th><th>Lineup</th><th>Jersey</th><th>Actions</th></tr></thead>
      <tbody>${data.teams.map(t => `
        <tr>
          <td>${esc(t.team_name)}</td><td>${esc(t.sport_name)}</td><td>${esc(t.formation || "")}</td>
          <td>${esc(t.student_name || "—")}</td><td>${esc(t.position_name || "—")}</td>
          <td>${esc(t.lineup_status || "—")}</td><td>${esc(t.jersey_number || "—")}</td>
          <td>${t.team_member_id ? `<button class="btn small" onclick="removeTeamMember(${t.team_member_id})">Remove</button>` : "—"}</td>
        </tr>`).join("")}</tbody></table>`;
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Attaches an event handler.
document.getElementById("teamForm")?.addEventListener("submit", async event => {
  // Executes the current JavaScript statement/block.
  event.preventDefault();
  // Starts error-handling logic.
  try {
    // Sends a request to the backend.
    await api("coach_team.php", {
      method: "POST",
      // Converts JavaScript data to JSON text.
      body: JSON.stringify({
        // Finds an HTML element in the DOM.
        sport_id: Number(document.getElementById("team_sport_id").value),
        // Finds an HTML element in the DOM.
        team_name: document.getElementById("team_name").value,
        // Finds an HTML element in the DOM.
        formation: document.getElementById("team_formation").value
      // Executes the current JavaScript statement/block.
      })
    // Executes the current JavaScript statement/block.
    });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", "Team created.");
    // Executes the current JavaScript statement/block.
    event.target.reset();
    // Executes the current JavaScript statement/block.
    loadCoachTeams();
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
});

// Defines a reusable JavaScript function.
async function assignTeamMember() {
  // Starts error-handling logic.
  try {
    // Sends a request to the backend.
    await api("coach_team_member.php", {
      method: "POST",
      // Converts JavaScript data to JSON text.
      body: JSON.stringify({
        // Finds an HTML element in the DOM.
        team_id: Number(document.getElementById("member_team_id").value),
        // Finds an HTML element in the DOM.
        student_id: Number(document.getElementById("member_student_id").value),
        // Finds an HTML element in the DOM.
        position_id: Number(document.getElementById("member_position_id").value),
        // Finds an HTML element in the DOM.
        lineup_status: document.getElementById("member_status").value,
        // Finds an HTML element in the DOM.
        jersey_number: document.getElementById("member_jersey").value,
        // Finds an HTML element in the DOM.
        formation_slot: document.getElementById("member_slot").value
      // Executes the current JavaScript statement/block.
      })
    // Executes the current JavaScript statement/block.
    });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", "Player assigned to team.");
    // Executes the current JavaScript statement/block.
    loadCoachTeams();
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

// Defines a reusable JavaScript function.
async function removeTeamMember(id) {
  // Checks a condition before continuing.
  if (!confirm("Remove this player from the team?")) return;
  // Starts error-handling logic.
  try {
    // Sends a request to the backend.
    await api(`coach_team_member.php?team_member_id=${id}`, { method: "DELETE" });
    // Executes the current JavaScript statement/block.
    showMessage("coachMessage", "Player removed from team.");
    // Executes the current JavaScript statement/block.
    loadCoachTeams();
  // Executes the current JavaScript statement/block.
  } catch (error) { showMessage("coachMessage", error.message, "error"); }
// Executes the current JavaScript statement/block.
}

/* V3 navigation QoL: highlight the section currently in view. */
/* COACH TABLE FILTER */
// Filters already-rendered table rows on the coach dashboard; no database data is changed.
document.addEventListener("DOMContentLoaded", () => {
  const filter = document.getElementById("coachTableFilter");
  if (!filter) return;

  filter.addEventListener("input", () => {
    const query = filter.value.trim().toLowerCase();
    document.querySelectorAll(".table-wrap tbody tr").forEach(row => {
      row.hidden = query && !row.textContent.toLowerCase().includes(query);
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  // Declares a JavaScript variable.
  const nav = document.querySelector(".quick-nav");
  // Checks a condition before continuing.
  if (!nav) return;
  // Declares a JavaScript variable.
  const links = [...nav.querySelectorAll("a[href^='#']")];
  // Declares a JavaScript variable.
  const sections = links.map(a => document.querySelector(a.getAttribute("href"))).filter(Boolean);
  // Declares a JavaScript variable.
  const setActive = id => links.forEach(a => a.classList.toggle("active", a.getAttribute("href") === "#" + id));
  // Checks a condition before continuing.
  if ("IntersectionObserver" in window) {
    // Declares a JavaScript variable.
    const observer = new IntersectionObserver(entries => {
      // Declares a JavaScript variable.
      const visible = entries.filter(e => e.isIntersecting).sort((a,b) => b.intersectionRatio-a.intersectionRatio)[0];
      // Checks a condition before continuing.
      if (visible) setActive(visible.target.id);
    // Executes the current JavaScript statement/block.
    }, { rootMargin:"-20% 0px -65% 0px", threshold:[0,.25,.5,.75,1] });
    // Executes the current JavaScript statement/block.
    sections.forEach(s => observer.observe(s));
  // Executes the current JavaScript statement/block.
  }
  // Attaches an event handler.
  links.forEach(a => a.addEventListener("click", () => {
    // Executes the current JavaScript statement/block.
    setActive(a.getAttribute("href").slice(1));
  // Executes the current JavaScript statement/block.
  }));
// Executes the current JavaScript statement/block.
});


// Initialize small client-side accessibility and convenience enhancements after the page is ready.
document.addEventListener("DOMContentLoaded", initPasswordToggles);
