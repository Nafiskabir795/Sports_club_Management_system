/* Student v5: focused navigation, readable sessions, coach notes and accessible registration. */
(() => {
  'use strict';
  const $ = id => document.getElementById(id);
  const safe = value => esc(value == null || value === '' ? '—' : value);
  const dateText = date => date ? new Date(`${date}T12:00:00`).toLocaleDateString(undefined,{year:'numeric',month:'short',day:'numeric'}) : 'Date TBA';
  const empty = (title,detail) => `<div class="student-empty" role="status"><strong>${safe(title)}</strong><p>${safe(detail)}</p></div>`;
  const sessionDate = s => new Date(`${s.session_date}T${s.start_time || '00:00:00'}`);
  const isCancelled = s => String(s.status).toLowerCase() === 'cancelled';
  function viewFromHash() {
    const allowed = ['overview','schedule-section','selection-section','team-section','performance-section'];
    const hash = location.hash.slice(1);
    return allowed.includes(hash) ? hash : 'overview';
  }
  function activateStudentView() {
    if (!$('studentNav')) return;
    const active = viewFromHash();
    document.querySelectorAll('.student-view').forEach(el => { el.hidden = el.id !== active; });
    document.querySelectorAll('#studentNav a').forEach(a => {
      const selected = a.getAttribute('href') === `#${active}`;
      a.classList.toggle('active', selected);
      if(selected) a.setAttribute('aria-current','page'); else a.removeAttribute('aria-current');
    });
    const titles = {'overview':'My profile & sports','schedule-section':'Schedule & attendance','selection-section':'My selection status','team-section':'My teams','performance-section':'Performance & feedback'};
    if ($('dashboardTitle')) $('dashboardTitle').textContent = titles[active];
    $('studentNav').classList.remove('mobile-open');
    $('studentMenuToggle')?.setAttribute('aria-expanded','false');
  }
  function showSchedule() {
    if (!$('schedule')) return;
    const now = new Date();
    const filter = $('studentScheduleFilter')?.value || 'upcoming';
    const sessions = (studentDashboardState.schedule || []).filter(s => {
      if(filter === 'all') return true;
      if(filter === 'cancelled') return isCancelled(s);
      if(isCancelled(s)) return false;
      return filter === 'upcoming' ? sessionDate(s) >= now : sessionDate(s) < now;
    }).sort((a,b) => filter === 'completed' ? sessionDate(b)-sessionDate(a) : sessionDate(a)-sessionDate(b));
    $('schedule').innerHTML = sessions.length ? sessions.map(s => `
      <article class="student-session-card">
        <div class="student-session-head"><span class="tag">${safe(s.sport_name)}</span><span class="tag ${s.attendance === 'Present' ? 'success' : s.attendance === 'Absent' ? 'danger' : 'warning'}">${safe(s.attendance || 'Pending')}</span></div>
        <h3>${safe(s.title)}</h3><p class="muted">${safe(s.session_type)} · ${dateText(s.session_date)} · ${safe(s.start_time)}–${safe(s.end_time)}</p>
        <p>📍 ${safe(s.location || 'Location TBA')}</p><p class="muted">${safe(s.coach_name || 'Coach TBA')}</p>
        <span class="tag">${safe(s.status)}</span>
      </article>`).join('') : empty('No sessions in this category','Try another filter or check back when your coach schedules a practice.');
  }
  function showPerformance() {
    if (!$('performance')) return;
    const filter = $('studentPerformanceFilter')?.value || 'all';
    const rows = (studentDashboardState.performance || []).filter(r => filter === 'all' || String(r.sport_name).toLowerCase() === filter).sort((a,b)=>`${b.session_date} ${b.start_time}`.localeCompare(`${a.session_date} ${a.start_time}`));
    const valid = rows.filter(r => r.overall_score !== null && r.overall_score !== undefined && r.overall_score !== '').slice().reverse();
    const chart = $('studentPerformanceChart');
    if(chart) {
      if(valid.length) {
        const w=620,h=185,pad=30;
        const points=valid.map((r,i)=>({x:pad+(valid.length===1?(w-2*pad)/2:i*(w-2*pad)/(valid.length-1)),y:h-pad-(Math.min(100,Math.max(0,Number(r.overall_score)))/100)*(h-2*pad)}));
        chart.innerHTML = `<h3>Overall score trend</h3><svg viewBox="0 0 ${w} ${h}" role="img" aria-label="Scores over ${valid.length} recorded sessions, oldest first" preserveAspectRatio="xMidYMid meet"><path d="M${pad} ${pad}V${h-pad}H${w-pad}" stroke="currentColor" fill="none" opacity=".4"/><path d="${points.map((p,i)=>`${i?'L':'M'}${p.x},${p.y}`).join(' ')}" stroke="#15803d" stroke-width="3" fill="none"/>${points.map((p,i)=>`<circle cx="${p.x}" cy="${p.y}" r="5" fill="#15803d"><title>${safe(valid[i].title)}: ${safe(valid[i].overall_score)}/100</title></circle>`).join('')}<text x="3" y="${pad+4}" font-size="12" fill="currentColor">100</text><text x="12" y="${h-pad}" font-size="12" fill="currentColor">0</text></svg><p class="muted">${valid.length} recorded score${valid.length===1?'':'s'} · Each point represents a session.</p>`;
      } else chart.innerHTML = '';
    }
    $('performance').innerHTML = rows.length ? rows.map(r => {
      const ignore = new Set(['performance_id','session_id','student_id','sport_name','session_type','session_number','title','session_date','start_time','end_time','overall_score','coach_notes']);
      const metrics = Object.entries(r).filter(([k,v])=>!ignore.has(k)&&v!==null&&v!=='').map(([k,v])=>`<div><small>${safe(k.replaceAll('_',' '))}</small><strong>${safe(v)}</strong></div>`).join('');
      return `<details class="student-result"><summary><span><strong>${safe(r.sport_name)} · ${safe(r.title)}</strong><small>${dateText(r.session_date)} · ${safe(r.session_type)}</small></span><span class="student-result-score">${r.overall_score == null ? 'Not scored' : `${safe(r.overall_score)}/100`}</span></summary><div class="student-result-body"><h4>Coach feedback</h4><p>${safe(r.coach_notes || 'No feedback has been recorded for this session.')}</p><h4>Session statistics</h4><div class="student-metrics">${metrics || '<p>No additional statistics recorded.</p>'}</div></div></details>`;
    }).join('') : empty('No performance results yet','Your coach’s recorded results and feedback will appear here.');
  }
  // Wrap existing loaders rather than altering coach-side functionality.
  if (typeof loadSchedule === 'function') {
    const original = loadSchedule;
    loadSchedule = async function(...args) { await original(...args); showSchedule(); };
  }
  if (typeof loadPerformance === 'function') {
    const original = loadPerformance;
    loadPerformance = async function(...args) { await original(...args); showPerformance(); };
  }
  function setupRegistrationWizard() {
    const form = $('studentRegistrationForm'); if(!form) return;
    const all = [...form.querySelectorAll('.field,.form-section-title,.form-help,#studentSportChoices')];
    const groups = [ ['sr_name','sr_phone','sr_email','sr_password'], ['sr_age','sr_blood','sr_weight','sr_height','sr_attributes'], ['studentSportChoices'], [] ];
    const stepOf = el => {
      if(el.id==='studentSportChoices' || el.closest('#studentSportChoices')) return 2;
      if(el.classList.contains('form-section-title')) return el.textContent.includes('Sport')?2:0;
      if(el.classList.contains('form-help')) return 2;
      const field = el.querySelector('input,select,textarea');
      return groups.findIndex(g=>g.includes(field?.id));
    };
    let step=0;
    const progress=[...document.querySelectorAll('.student-register-progress span')];
    function render() {
      all.forEach(el => {const group=stepOf(el);el.hidden=group>=0 && group!==step;});
      $('studentRegistrationReview').classList.toggle('hidden',step!==3);
      $('studentRegisterBack').hidden=step===0;
      $('studentRegisterNext').hidden=step===3;
      $('studentRegisterSubmit').hidden=step!==3;
      progress.forEach((el,i)=>el.classList.toggle('active',i===step));
      if(step===3) {
        const names=[['Name','sr_name'],['Email','sr_email'],['Phone','sr_phone'],['Age','sr_age']];
        const sports=[...form.querySelectorAll('.student-sport:checked')].map(el=>el.closest('.mini-card')?.querySelector('strong')?.textContent||el.value);
        $('studentRegistrationReview').innerHTML = `<h2>Review your information</h2>${names.map(([label,id])=>`<p><strong>${label}:</strong> ${safe($(id)?.value)}</p>`).join('')}<p><strong>Sports:</strong> ${safe(sports.join(', '))}</p><p class="muted">Your password is not displayed. Go back to correct any details.</p>`;
      }
    }
    function validate() {
      const fields=groups[step] || [];
      for(const id of fields) {const input=$(id);if(input?.matches('input,select,textarea')&&!input.checkValidity()){input.reportValidity();return false;}}
      if(step===2){const selected=[...form.querySelectorAll('.student-sport:checked')];if(!selected.length){showMessage('studentRegistrationMessage','Choose at least one sport.','error');return false;}for(const sport of selected){const position=$(`studentPosition_${sport.value}`)?.querySelector('select');if(position&&!position.value){position.reportValidity();showMessage('studentRegistrationMessage','Select a position for each sport.','error');return false;}}}
      return true;
    }
    $('studentRegisterNext').addEventListener('click',()=>{if(validate()){hideMessage('studentRegistrationMessage');step=Math.min(3,step+1);render();}});
    $('studentRegisterBack').addEventListener('click',()=>{step=Math.max(0,step-1);render();});
    form.addEventListener('submit',event=>{if(step!==3){event.preventDefault();return;}for(let i=0;i<3;i++){step=i;if(!validate()){event.preventDefault();render();return;}}step=3;render();},true);
    render();
  }
  document.addEventListener('DOMContentLoaded',()=>{
    if($('studentNav')) {
      activateStudentView(); window.addEventListener('hashchange',activateStudentView);
      $('studentMenuToggle')?.addEventListener('click',()=>{const open=$('studentNav').classList.toggle('mobile-open');$('studentMenuToggle').setAttribute('aria-expanded',String(open));});
      $('studentScheduleFilter')?.addEventListener('change',showSchedule);
      $('studentPerformanceFilter')?.addEventListener('change',showPerformance);
    }
    setupRegistrationWizard();
  });
})();
