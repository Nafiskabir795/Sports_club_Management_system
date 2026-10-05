/* Progressive enhancement: all existing app.js workflows remain available. */
let coachCalendarSessions = [];
function initCoachV3() {
  const menu = document.getElementById('coachMenuToggle');
  const nav = document.getElementById('coachSidebar');
  menu?.addEventListener('click', () => { const open = nav.classList.toggle('is-open'); menu.setAttribute('aria-expanded', String(open)); });
  nav?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
    nav.querySelectorAll('a').forEach(a => a.classList.toggle('active', a === link));
    nav.classList.remove('is-open'); menu?.setAttribute('aria-expanded','false');
  }));
  const month = document.getElementById('calendarMonth');
  if(month) month.value = new Date().toISOString().slice(0,7);
}
async function refreshCoachV3() { await Promise.allSettled([loadCoachAnalytics(), loadCoachCalendar(), populateAttendanceSessions()]); }
async function populateAttendanceSessions() {
  const select = document.getElementById('attendanceSessionId'); if(!select) return;
  const previous = select.value;
  try {
    const data = await api('coach_sessions.php');
    select.innerHTML = '<option value="">Choose a session…</option>' + data.sessions.map(s => `<option value="${Number(s.session_id)}">${esc(s.sport_name || 'Sport')} · ${esc(s.title)} · ${esc(s.session_date)}</option>`).join('');
    if([...select.options].some(o=>o.value===previous)) select.value=previous;
  } catch(e) { showMessage('coachMessage',e.message,'error'); }
}
function markAllAttendance(value) { document.querySelectorAll('#attendanceTable select[id^="att-"]').forEach(s=>s.value=value); }
async function saveBulkAttendance() {
  const sessionId = Number(document.getElementById('attendanceSessionId').value);
  const rows = [...document.querySelectorAll('#attendanceTable select[id^="att-"]')].map(s=>({student_id:Number(s.id.slice(4)),attendance:s.value}));
  if(!sessionId || !rows.length) return showMessage('coachMessage','Choose a session with participants.','error');
  const btn=document.getElementById('bulkAttendanceSave'); btn.disabled=true;
  try { const result=await api('coach_attendance_bulk.php',{method:'POST',body:JSON.stringify({session_id:sessionId,participants:rows})});showMessage('coachMessage',result.message);loadCoachAnalytics(); }
  catch(e){showMessage('coachMessage',e.message,'error');}finally{btn.disabled=false;}
}
async function loadCoachAnalytics() {
  const root=document.getElementById('coachAnalyticsCards');if(!root)return;
  try {
    const d=await api('coach_analytics.php');
    const cards=[['Assigned sessions',d.summary.sessions],['Registered players',d.summary.players],['Attendance rate',d.summary.attendance_rate+'%'],['Average performance',d.summary.avg_score===null?'—':d.summary.avg_score]];
    root.innerHTML=cards.map(([name,value])=>`<div class="stat-card"><span>${esc(name)}</span><strong>${esc(value)}</strong></div>`).join('');
    document.getElementById('coachAnalyticsSports').innerHTML=d.sports.map(s=>`<div class="analytics-row"><strong>${esc(s.sport_name)}</strong><div class="analytics-track"><div style="width:${Math.min(100,Math.max(0,Number(s.avg_score)||0))}%"></div></div><span>${s.avg_score===null?'No scores':esc(s.avg_score)+'/100'}</span></div>`).join('') || '<p class="muted">No performance records yet.</p>';
    document.getElementById('coachMissingPerformance').innerHTML=d.missing.length?`<table><thead><tr><th>Player</th><th>Practice</th><th>Date</th></tr></thead><tbody>${d.missing.map(r=>`<tr><td>${esc(r.name)}</td><td>${esc(r.title)}</td><td>${esc(r.session_date)}</td></tr>`).join('')}</tbody></table>`:'<p class="muted">No outstanding performance records for assigned practices.</p>';
  } catch(e) { root.innerHTML=`<p class="muted">Analytics unavailable: ${esc(e.message)}</p>`; }
}
async function loadCoachCalendar() {
  try {const d=await api('coach_sessions.php');coachCalendarSessions=d.sessions;renderCoachCalendar();}catch(e){showMessage('coachMessage',e.message,'error');}
}
function renderCoachCalendar() {
  const root=document.getElementById('coachCalendarGrid'), value=document.getElementById('calendarMonth')?.value;
  if(!root||!value)return;
  const [y,m]=value.split('-').map(Number),first=new Date(y,m-1,1),days=new Date(y,m,0).getDate();
  const byDay=new Map();coachCalendarSessions.filter(s=>String(s.session_date).slice(0,7)===value).forEach(s=>{const day=Number(String(s.session_date).slice(8,10));byDay.set(day,[...(byDay.get(day)||[]),s]);});
  root.innerHTML=['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(d=>`<div class="calendar-head">${d}</div>`).join('')+Array.from({length:(first.getDay()+6)%7},()=>'<div class="calendar-cell calendar-empty"></div>').join('')+Array.from({length:days},(_,i)=>{const n=i+1;return `<div class="calendar-cell"><strong>${n}</strong>${(byDay.get(n)||[]).map(s=>`<div class="calendar-event" title="${esc(s.title)}">${esc(s.sport_name||'Sport')} · ${esc(s.title)}</div>`).join('')}</div>`;}).join('');
}
// Expose calendar data to the progressive-enhancement mobile agenda renderer.
Object.defineProperty(window, 'coachCalendarSessions', {get: () => coachCalendarSessions});
