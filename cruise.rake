require 'timeout'
require 'socket'

CRUISE_BUILD = "CRUISE_BUILD=#{ENV['CRUISE_BUILD']}"
GATEWAY_ROOT = File.dirname(__FILE__) + "/../gateway" unless defined?(GATEWAY_ROOT)
PORT = ENV['GATEWAY_PORT'] || 3000
PID_FILE = "/tmp/gateway_server_#{PORT}.pid"

desc "prep the gateway (including git clone and db reset) and run tests"
task :cruise do
  begin
    Rake::Task["prep_gateway"].invoke
    Rake::Task["test"].invoke
  ensure
    Rake::Task["stop_gateway"].invoke
  end
end

task :prep_gateway do
  Dir.chdir(GATEWAY_ROOT) do
    sh "git pull"
    sh "env RAILS_ENV=integration #{CRUISE_BUILD} rake db:migrate:reset --trace"
    sh "env RAILS_ENV=integration #{CRUISE_BUILD} ruby script/populate_data"
    Rake::Task[:start_gateway].invoke
  end
end

task :start_gateway do
  Dir.chdir(GATEWAY_ROOT) do
    spawn_server(PID_FILE, PORT, "integration")
  end
end

task :stop_gateway do
  Dir.chdir(GATEWAY_ROOT) do
    shutdown_server(PID_FILE)
  end
end

def spawn_server(pid_file, port, environment="test")
  FileUtils.rm(pid_file) if File.exist?(pid_file)
  command = "mongrel_rails start --environment #{environment} --daemon --port #{port} --pid #{pid_file}"

  sh command
  puts "== waiting for web server - port: #{port}"
  TCPSocket.wait_for_service :host => "127.0.0.1", :port => port
end

def shutdown_server(pid_file)
  10.times { unless File.exists?(pid_file); sleep 1; end }
  puts "\n== killing web server - pid: #{File.read(pid_file).to_i}"
  Process.kill "TERM", File.read(pid_file).to_i
end

TCPSocket.class_eval do
  def self.wait_for_service(options)
    Timeout::timeout(options[:timeout] || 20) do
      loop do
        begin
          socket = TCPSocket.new(options[:host], options[:port])
          socket.close
          return
        rescue Errno::ECONNREFUSED
          sleep 0.5
        end
      end
    end
  end
end
