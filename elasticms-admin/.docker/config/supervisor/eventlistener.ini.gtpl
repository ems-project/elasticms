[eventlistener:{{ .Env.ELASTICMS_INSTANCE_NAME }}]
; base-php's listener: it echoes the event header and payload only under DEBUG.
command=/usr/local/bin/supervisord-event-listener.py /opt/sbin/ems-jobs/{{ .Env.ELASTICMS_INSTANCE_NAME }}
events=TICK_60
; Nothing restarts the container when the listener dies, and the instance's jobs
; stop with it. Bring it back.
autorestart=true
; stdout is how an event listener answers supervisor -- READY, RESULT -- not a
; log stream. stderr is the job's own output.
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
