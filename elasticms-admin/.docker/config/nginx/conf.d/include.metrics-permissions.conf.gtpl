# Localhost: Allow internal health checks and local container access
allow 127.0.0.1;

# Docker Networking: Allow access from host and bridge networks (RFC1918)
allow 172.16.0.0/12; 
allow 192.168.0.0/16;

# Kubernetes Infrastructure: Allow Pod-to-Pod communication (Pod CIDR)
allow 10.0.0.0/8;

# Proxy/Ingress: Optional - Ensure real IP handling is configured if using X-Forwarded-For
# real_ip_header X-Forwarded-For;