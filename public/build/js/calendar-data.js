document.addEventListener(`DOMContentLoaded`,function(){let e=document.getElementById(`calendar`);if(!e)return;e.style.overflow=`hidden`;let t=()=>{document.querySelector(`.fc-event-popup`)?.remove()},n=new Date,r=e=>e.toISOString().split(`T`)[0],i=e=>{let t=new Date(n);return t.setDate(n.getDate()+e),r(t)},a=[{title:`Team Hall YT`,start:i(0),backgroundColor:`#EEF2FF`,borderColor:`#6366F1`,textColor:`#4338CA`},{title:`Training Workshop`,start:`${i(1)}T09:30:00`,end:`${i(1)}T10:30:00`,backgroundColor:`#FCE7F3`,borderColor:`#EC4899`,textColor:`#BE185D`},{title:`Wellness Session`,start:i(2),backgroundColor:`#ECFEFF`,borderColor:`#06B6D4`,textColor:`#0E7490`},{title:`Team Activity`,start:i(3),backgroundColor:`#FFF7ED`,borderColor:`#F97316`,textColor:`#9A3412`},{title:`Weekly Sync`,start:i(4),allDay:!0,backgroundColor:`#F0FDF4`,borderColor:`#22C55E`,textColor:`#15803D`},{title:`Weekly Sync`,start:`${i(4)}T09:00:00`,end:`${i(4)}T10:00:00`,backgroundColor:`#F0FDF4`,borderColor:`#22C55E`,textColor:`#15803D`},{title:`Project Demo`,start:i(5),allDay:!0,backgroundColor:`#FAF5FF`,borderColor:`#A855F7`,textColor:`#7E22CE`},{title:`Project Demo`,start:`${i(5)}T09:00:00`,end:`${i(5)}T10:30:00`,backgroundColor:`#FAF5FF`,borderColor:`#A855F7`,textColor:`#7E22CE`}],o=new FullCalendar.Calendar(e,{initialView:`dayGridMonth`,height:650,contentHeight:620,handleWindowResize:!0,expandRows:!0,eventDisplay:`block`,events:a,customButtons:{fcToday:{text:`Today`,click(){o.today()}},addEvent:{text:`+ New Event`,click(){document.querySelector(`[data-hs-overlay="#add-event"]`)?.click()}}},eventClick:function(e){e.jsEvent.preventDefault(),t();let n=e.event,r=document.createElement(`div`);r.className=`fc-event-popup fixed z-[9999] top-0 left-0 size-full overflow-x-hidden overflow-y-auto flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm flex-wrap`,r.innerHTML=`
        <div class="max-w-[400px] min-w-[300px] w-full p-6 bg-white border border-border-color rounded-lg shadow-xl">
          <div class="flex justify-between items-center mb-5 pb-5 border-b border-border-color">
            <h4>Event Details</h4>
            <button type="button" class="popup-close size-7 inline-flex justify-center items-center rounded-full border border-border-color bg-white text-gray-900 text-base hover:bg-danger hover:border-danger hover:text-white dark:hover:text-dark focus:outline-hidden focus:bg-danger cursor-pointer">
              <i class="icon-x"></i>
            </button>
          </div>
          <div class="mb-5 pb-5 border-b border-border-color">
            <p class="font-semibold text-dark mb-2">${n.title}</p>
            <p class="mb-4 text-sm text-gray-600">An in company training workshop focused on enhancing employee skills through practical, hands on learning.</p>
            <p class="flex items-center gap-2 mb-3 text-sm">
              <i class="icon-calendar text-dark"></i>
              <span>${n.start.toLocaleDateString(`en-IN`,{weekday:`long`,year:`numeric`,month:`long`,day:`numeric`})}</span>
            </p>
            <p class="flex items-center gap-2 mb-3 text-sm">
              <i class="icon-clock text-dark"></i>
              ${n.allDay?`All Day`:n.start.toLocaleTimeString([],{hour:`2-digit`,minute:`2-digit`})}
            </p>
            <p class="flex items-center gap-2 text-sm">
              <i class="icon-map-pin text-dark"></i>
              Room 2A
            </p>
          </div>
          <div class="flex justify-between items-center">
            <div class="avatar-list-stacked">
              <img src="build/img/avatar/avatar-27.jpg" alt="JS" class="w-6 h-6 inline-flex items-center justify-center hover:-translate-y-[0.188rem] hover:z-1 transition-transform duration-150 ease-in-out -me-3.5 rounded-full border border-border-color">
              <img src="build/img/avatar/avatar-28.jpg" alt="AR" class="w-6 h-6 inline-flex items-center justify-center hover:-translate-y-[0.188rem] hover:z-1 transition-transform duration-150 ease-in-out -me-3.5 rounded-full border border-border-color">
              <img src="build/img/avatar/avatar-29.jpg" alt="KM" class="w-6 h-6 inline-flex items-center justify-center hover:-translate-y-[0.188rem] hover:z-1 transition-transform duration-150 ease-in-out -me-3.5 rounded-full border border-border-color">
              <span class="w-6 h-6 inline-flex items-center justify-center hover:-translate-y-[0.188rem] text-[12px] bg-light text-dark -me-3.5 rounded-full border border-border-color"> 1+ </span>
            </div>
            <div class="flex items-center gap-2">
              <button data-hs-overlay="#edit-event" class="size-7 text-sm flex items-center cursor-pointer justify-center bg-white border border-border-color text-dark hover:text-primary rounded-full">
                <i class="icon-pencil-line"></i>
              </button>
              <a href="#" class="size-7 text-sm flex items-center justify-center bg-white border border-border-color text-dark hover:text-danger rounded-full">
                <i class="icon-trash-2"></i>
              </a>
            </div>
          </div>
        </div>
      `,document.body.appendChild(r),r.querySelector(`.popup-close`).onclick=t,setTimeout(()=>{window.addEventListener(`click`,function e(n){r.contains(n.target)||(t(),window.removeEventListener(`click`,e))},{capture:!0})},10)},headerToolbar:{start:`prev,title,next`,center:`dayGridMonth,dayGridWeek,dayGridDay`,end:`fcToday addEvent`},views:{dayGridMonth:{displayEventTime:!1,dayMaxEvents:!0},dayGridWeek:{displayEventTime:!0},dayGridDay:{displayEventTime:!0}},eventDidMount(e){let t=e.view.type===`dayGridMonth`,n=e.event.title;if(t&&!e.event.allDay&&(n===`Weekly Sync`||n===`Project Demo`)){e.el.style.display=`none`;return}if(t&&(n===`Strategy Meeting`||n===`Tech Review`)){e.el.style.display=`none`;return}e.el.style.backgroundColor=e.event.backgroundColor,e.el.style.borderColor=e.event.borderColor,e.el.style.color=e.event.textColor,e.el.style.borderRadius=`6px`;let r=e.el.querySelector(`.fc-event-main`);r&&(r.style.padding=`2px 4px`,r.style.fontWeight=`600`)}});o.render()});