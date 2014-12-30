set :application, "rasmus contributors"
set :domain,      "ec2-50-112-141-57.us-west-2.compute.amazonaws.com"
set :deploy_to,   "/var/www/contributors"
set :app_path,    "app"
set :user,        "ubuntu"
set :use_sudo,      true

ssh_options[:keys] = "~/.ssh/keys/aws-us-west.pem"
ssh_options[:forward_agent] = true
ssh_options[:auth_methods] = %w(publickey)

set :scm,           :git
set :repository,    "git@github.com:roinir237/rasmus_contributors.git"

set :model_manager, "doctrine"

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server

set :keep_releases,  3

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads", "vendor"]
set :use_composer, true
set :update_vendors, true

set :pip_reqs, "worker/requirements.txt"

namespace :python do
    desc "install all pip dependencies"
    task :install, roles: :app do
        capifony_pretty_print "--> Installing python dependencies using pip"
        run "#{try_sudo} pip install -r #{current_path}/#{pip_reqs}"
        capifony_puts_ok
    end

    desc "restart celery processes"
    task :restart, roles: :app do
        capifony_pretty_print "--> Restarting supervisord"
        run "#{try_sudo} service supervisor restart"
        capifony_puts_ok
    end
end