# Laravel App - Tasks & Bugs

## 🔴 Critical Bugs (Breaking the app)
- [ ] Project view should be in a list and admins should have CRUD access, with users able to add new projects and edit if they are the project owner or project manager
- [ ] View button doesnt work on '/admin/users'. Get the folowing error: 'syntax error, unexpected end of file, expecting "elseif" or "else" or "endif"'

## 🟡 Non-Critical Bugs (App works but has issues)  
- [ ] On the Dashboard, the quick stats are showing error the following "@try 0 @catch(\Exception $e) 0 @endtry"
- [ ] Project permalink should be a hyperlink not just a stub string. This needs to be easy for people to use


## 🟢 Features to Implement for Paint Catalog
- [ ] Change the Paint catalog so all fields are in columns, with the option to enable to diable certain columns (default being all)
- [ ] Ability for admins to select multiple paints and delete them
- [ ] Ability for filter paint based on Manufacturer
- [ ] Ability for filter paint based on color - so all shades of blue could be filtered
- [ ] import function for paint in CSV format

## 🟢 Features to Implement for Projects
- [ ] Project location should be searchable, ideally with integration with google maps
- [ ] The scope included a concept of paint bundles, so you can shop from the paint catalog and add to bundles that are associated with a project. 

## 📝 Notes
- Using Laravel 10.x
- MySQL database
- Needs to move to a hosted server soon, but currently only in development on my PC
- I want to move to Claude making all the changes to implement fixes and new functionality 
- I'm a beginner vibe coder, so tell me if there's a better way to feed claude instructions.
- There is key functionality from the original scope that is missing such as paint bundles, the ability to upload inspiration photos, sketches and photos to a project.
I am missing th econcept of paint bundles
- If you need more information on the scope, ask me.