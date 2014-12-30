set :application, "rasmus contributors"
set :domain,      "ec2-50-112-141-57.us-west-2.compute.amazonaws.com"
set :user,        "ubuntu"
set :deploy_to,   "/var/www/contributors"
set :app_path,    "app"

ssh_options[:forward_agent] = true
ssh_options[:keys] = "~/.ssh/keys/aws-us-west.pem"

set :scm,           :git
set :repository,    "git@github.com:roinir237/rasmus_contributors.git"

set :model_manager, "doctrine"

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server

set :use_sudo,      true
set :keep_releases,  3

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads", "vendor"]
set :use_composer, true
set :update_vendors, true