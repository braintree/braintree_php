Project.configure do |project|
  project.scheduler.polling_interval = 1.minute

  case project.name
  when "client_library_php_integration_master"
    project.build_command = "CRUISE_BUILD=#{project.name} GATEWAY_PORT=8010 SPHINX_PORT=8322 rake cruise --trace"
    project.triggered_by :gateway_master
  end
end
