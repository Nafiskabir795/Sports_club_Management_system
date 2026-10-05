/* v4 progressive enhancement: no schema changes or framework dependencies. */
(() => {
  'use strict';
  const $ = id => document.getElementById(id);
  const sections = ['coach-overview','students-section','coaches-section','sessions-section','attendance-section','performance-section','selection-section','teams-section','coach-analytics','coach-calendar'];
  const titles = {'coach-overview':['Overview','Your club at a glance. Select a section to manage records.'],'students-section':['Players','Find, add and update registered players.'],'coaches-section':['Coaches','Manage coach accounts.'],'sessions-section':['Sessions','Schedule and manage practices and testing.'],'attendance-section':['Attendance','Choose a session and mark your roster.'],'performance-section':['Performance','Record statistics and move to the next player.'],'selection-section':['Selection','Record testing scores and selection outcomes.'],'teams-section':['Teams','Build squads with sport-appropriate players and positions.'],'coach-analytics':['Analytics','Explore participation and performance.'],'coach-calendar':['Calendar','See upcoming sessions in calendar or agenda view.']};
  let options = null, currentView = '', dirty = false;
  const escText = s => String(s ?? '');
  function setView(id, push = true) {
    if (!sections.includes(id)) id = 'coach-overview';
    if (dirty && id !== currentView && !confirm('You have unsaved changes. Leave this section?')) return;
    currentView = id;
    document.body.classList.add('coach-focused');
    sections.forEach(key => { const node=$(key); if (node) {node.hidden = key !== id; node.classList.toggle('coach-current-view',key===id);} });
    document.querySelectorAll('#coachSidebar a[href^="#"]').forEach(a => {const active=a.getAttribute('href')==='#'+id;a.classList.toggle('active',active); if(active)a.setAttribute('aria-current','page');else a.removeAttribute('aria-current');});
    $('coachViewTitle').textContent=titles[id][0]; $('coachViewDescription').textContent=titles[id][1];
    $('coachBackOverview').hidden=id==='coach-overview';
    $('coachQuickFind').hidden=!['students-section','coaches-section','sessions-section','selection-section','teams-section'].includes(id);
    document.querySelector('.coach-workflow').hidden=id!=='coach-overview';
    if(push) history.replaceState(null,'','#'+id);
    $('coachSidebar')?.classList.remove('is-open');$('coachMenuToggle')?.setAttribute('aria-expanded','false');
    window.scrollTo({top:0,behavior:'instant'});
  }
  const opt = (value,label) => { const o=document.createElement('option');o.value=String(value);o.textContent=label;return o; };
  function fill(id, rows, key, label, placeholder) {
    const el=$(id);if(!el)return;const prior=el.value;el.replaceChildren(opt('',placeholder));rows.forEach(r=>el.appendChild(opt(r[key],label(r))));if([...el.options].some(o=>o.value===prior))el.value=prior;
  }
  function updateLinked() {
    if(!options)return;
    const selectionSport=Number($('selectionSportId').value);
    fill('selectionStudentId',options.players.filter(p=>!selectionSport||Number(p.sport_id)===selectionSport),'student_id',p=>p.name+' ('+p.student_code+')','Choose player…');
    fill('selectionTestingSessionId',options.sessions.filter(s=>s.session_type==='Testing'&&(!selectionSport||Number(s.sport_id)===selectionSport)),'session_id',s=>s.title+' · '+s.session_date,'Choose testing session…');
    const team=options.teams.find(t=>String(t.team_id)===$('member_team_id').value);
    const sport=team?Number(team.sport_id):0;
    fill('member_student_id',options.players.filter(p=>sport&&Number(p.sport_id)===sport),'student_id',p=>p.name+' ('+p.student_code+')',sport?'Choose player…':'Choose a team first…');
    fill('member_position_id',options.positions.filter(p=>sport&&Number(p.sport_id)===sport),'position_id',p=>p.position_name,sport?'Choose position…':'Choose a team first…');
  }
  async function refreshOptions() {
    try {
      const d=await api('coach_ui_options.php');options=d;
      fill('selectionSportId',d.sports,'sport_id',s=>s.sport_name,'Choose sport…');
      fill('team_sport_id',d.sports,'sport_id',s=>s.sport_name,'Choose sport…');
      fill('member_team_id',d.teams,'team_id',t=>t.team_name,'Choose team…');
      updateLinked();
    } catch(e) { console.warn('Form choices unavailable',e);const msg=$('coachMessage');if(msg){msg.textContent='Could not load form choices. Refresh the page and check the database connection.';msg.classList.remove('hidden');} }
  }
  function labelControls() {
    document.querySelectorAll('#studentForm input:not([type=hidden]),#coachForm input:not([type=hidden]),#sessionForm input:not([type=hidden]),#teamForm input:not([type=hidden])').forEach(input=>{
      if(input.id&&!input.getAttribute('aria-label'))input.setAttribute('aria-label',input.placeholder||input.id.replaceAll('_',' '));
    });
  }
  function makeAgenda() {
    const grid=$('coachCalendarAgenda'),month=$('calendarMonth')?.value;if(!grid||!month||!Array.isArray(window.coachCalendarSessions))return;
    const rows=window.coachCalendarSessions.filter(s=>String(s.session_date).slice(0,7)===month).sort((a,b)=>String(a.session_date).localeCompare(String(b.session_date)));
    grid.replaceChildren();if(!rows.length){const p=document.createElement('p');p.className='muted';p.textContent='No sessions scheduled this month.';grid.append(p);return;}
    rows.forEach(s=>{const item=document.createElement('div');item.className='agenda-item';const date=document.createElement('strong');date.textContent=String(s.session_date);const detail=document.createElement('span');detail.textContent=[s.sport_name,s.title,s.start_time].filter(Boolean).join(' · ');item.append(date,detail);grid.append(item);});
  }
  function init() {
    document.querySelectorAll('#coachSidebar a[href^="#"]').forEach(a=>a.addEventListener('click',e=>{e.preventDefault();setView(a.hash.slice(1));}));
    document.querySelectorAll('[data-coach-view]').forEach(b=>b.addEventListener('click',()=>setView(b.dataset.coachView)));
    $('coachBackOverview')?.addEventListener('click',()=>setView('coach-overview'));
    // Existing calendar create-session button previously scrolled to a section that may be hidden.
    document.querySelector('#coach-calendar button[onclick]')?.addEventListener('click',e=>{e.preventDefault();e.stopImmediatePropagation();setView('sessions-section');},true);
    window.addEventListener('hashchange',()=>setView(location.hash.slice(1),false));
    document.querySelectorAll('#studentForm,#coachForm,#sessionForm,#teamForm,#performanceForm').forEach(form=>{
      form.addEventListener('input',()=>{dirty=true;});form.addEventListener('submit',()=>{dirty=false;});form.addEventListener('reset',()=>{dirty=false;});
    });
    window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue='';}});
    $('selectionSportId')?.addEventListener('change',updateLinked);$('member_team_id')?.addEventListener('change',updateLinked);
    $('calendarMonth')?.addEventListener('change',()=>setTimeout(makeAgenda,0));
    // Preserve existing refresh calls and update options after new team/student/session records are saved.
    ['loadCoachStudents','loadCoachSessions','loadCoachTeams'].forEach(name=>{const original=window[name];if(typeof original==='function')window[name]=async function(...args){const result=await original.apply(this,args);refreshOptions();return result;};});
    const originalCalendar=window.renderCoachCalendar;if(typeof originalCalendar==='function')window.renderCoachCalendar=function(...args){const r=originalCalendar.apply(this,args);makeAgenda();return r;};
    labelControls();setView(location.hash.slice(1)||'coach-overview',false);refreshOptions();
  }
  document.addEventListener('DOMContentLoaded',init);
})();
